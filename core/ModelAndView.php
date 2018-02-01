<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   ModelAndView
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/14
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

class ModelAndView
{
    private $m_viewName = NULL;
    private $m_model_attributes = array();
    private $m_locale = NULL;
    private $m_model_bindparams = array();

    private $m_attributeCallbacks = array();

    public function __construct($viewName = NULL)
    {
        $this->m_viewName = $viewName;
    }

    public function setLocale($locale)
    {
        $this->m_locale = $locale;
    }

    public function setViewName($viewName)
    {
        $this->m_viewName = $viewName;
    }

    public function getViewName()
    {
        return $this->m_viewName;
    }

    public function addAttribute($key, $value)
    {
        $this->m_model_attributes[$key] = $value;
    }

    public function addObject($key, &$object)
    {
        $this->m_model_attributes[$key] = $object;
    }

    public function &getAttribute($key)
    {
        return $this->m_model_attributes[$key];
    }

    public function &getAttributes()
    {
        return $this->m_model_attributes;
    }

    public function bindParam($key, &$value)
    {
        $this->m_model_bindparams[$key] = $value;
    }

    public function _addAttributeCallback($method, $param = NULL)
    {
        $this->m_attributeCallbacks[] = array($method, $param);
    }

    public static function _executeScript($__viewfilepath, &$request, &$response, &$__modelbindparams)
    {
        foreach($__modelbindparams as $key => $value)
        {
            $$key = &$__modelbindparams[$key];
        }
        ob_start();
        include($__viewfilepath);
        $content = ob_get_clean();
        return $content;
    }

    public function _execute(&$oCore, &$request, &$response)
    {
        $conststr_redirect = 'redirect:';
        if(strpos($this->m_viewName, $conststr_redirect) === 0)
        {
            $redirectUrl = substr($this->m_viewName, strlen($conststr_redirect));
            header("Location: ".$redirectUrl);
            return ;
        }
        if($this->m_viewName) {
            $viewfilepath = $oCore->getWorkDir() . '/view/' . $this->m_viewName . '.php';
            $viewfilestat = @stat($viewfilepath);
            if (!$viewfilestat) {
                $response->setStatus(HttpStatus::HTTP_NOT_FOUND);
                $viewfilepath = NULL;
            }
        }else{
            $viewfilepath = NULL;
        }

        if(!$this->m_locale)
        {
            $localeResolver = $oCore->_getAutoWiring()->getObject('localeResolver');
            $this->m_locale = $localeResolver->resolveLocale($request);
        }

        $response->beginOutput();

        if($viewfilepath)
        {
            $content = self::_executeScript($viewfilepath, $request, $response, $this->m_model_bindparams);

            $oReplaceCB = new \JsGreenTeaPHPFramework\core\ModelAndView\_ContentVariableReplaceCallback($oCore, $request->getAttributes(), $this->m_attributeCallbacks, $this->m_locale);
            $content = preg_replace_callback('/[\$#]{([^}]+)}/', array($oReplaceCB, "cbreplace"), $content);
            echo $content;
        }else if($response->getStatus() >= 400)
        {
            echo "ERROR : ".HttpStatus::getString($response->getStatus());
        }
    }
};

namespace JsGreenTeaPHPFramework\core\ModelAndView;

class _ContentVariableReplaceCallback
{
    public $oCore;
    public $oAutoWiring;
    public $oResourceManager;
    public $oMessageSource;
    public $attributes;
    public $locale;
    public $replacecount = 0;
    public $attributeCallbacks;

    public function __construct(&$oCore, &$attributes, &$attributeCallbacks, &$locale)
    {
        $this->oCore = $oCore;
        $this->oAutoWiring = $oCore->_getAutoWiring();
        $this->oResourceManager = $this->oAutoWiring->getObject('resourceManager');
        $this->attributes = $attributes;
        $this->attributeCallbacks = $attributeCallbacks;
        $this->locale = $locale;
    }

    private function realreplace($matches)
    {
        $this->replacecount++;
        $type = substr($matches[0], 0, 1);
        $result = NULL;
        foreach($this->attributeCallbacks as &$item)
        {
            $result = call_user_func($item[0], $type, $matches[1], $item[1]);
            if($result)
                return $result;
        }
        $result = $this->oCore->resolveRes($matches[0], $this->locale);
        if (!$result) {
            $result = @$this->attributes[$matches[1]];
        }
        return $result;
    }

    public function cbreplace($matches)
    {
        $content = $this->realreplace($matches);
        do {
            $oReplaceCB = new \JsGreenTeaPHPFramework\core\ModelAndView\_ContentVariableReplaceCallback($this->oCore, $this->attributes, $this->attributeCallbacks, $this->m_locale);
            $content = preg_replace_callback('/[\$#]{([^}]+)}/', array($oReplaceCB, "cbreplace"), $content);
        }while($oReplaceCB->replacecount > 0);
        return $content;
    }
}
