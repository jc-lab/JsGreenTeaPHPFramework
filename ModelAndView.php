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


namespace JsGreenTeaPHPFramework;

class ModelAndView
{
    private $m_viewName = NULL;
    private $m_model_attributes = array();
    private $m_locale = NULL;

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

    /*
    public static function _executeScript($__viewfilecontent, $request, $response, $session)
    {
        ob_start();
        eval($__viewfilecontent);
        $content = ob_get_clean();
        return $content;
    }
    */
    public static function _executeScript($__viewfilepath, $request, $response)
    {
        ob_start();
        include($__viewfilepath);
        $content = ob_get_clean();
        return $content;
    }

    public function _execute(&$oCore, &$request, &$response)
    {
        //$viewfilecontent = "";
        if($this->m_viewName) {
            $viewfilepath = $oCore->getWorkDir() . '/view/' . $this->m_viewName . '.php';
            $viewfilestat = stat($viewfilepath);
            if (!$viewfilestat) {
                $this->setStatus(HttpStatus::HTTP_NOT_FOUND);
                $viewfilepath = NULL;
            }/*else{
                $oFrameworkCache = $oCore->_getFrameworkCache();
                $viewfilecontent = $oFrameworkCache->getViewCache($this->m_lang, $this->m_viewName, $viewfilestat['mtime']);
                if(!$viewfilecontent)
                {
                    $viewfilecontent = file_get_contents($viewfilepath);
                    $oFrameworkCache->getViewCache($this->m_lang, $viewfilecontent, $viewfilestat['mtime']);
                }
            }*/
        }else{
            $viewfilepath = NULL;
        }

        if(!$this->m_locale)
        {
            $localeResolver = $oCore->_getAutoWiring()->getObject('localeResolver');
            $this->m_locale = $localeResolver->resolveLocale($request);
        }
        
        header($_SERVER['SERVER_PROTOCOL'].' '.$response->getStatus().' '.HttpStatus::getString($response->getStatus()));

        if($viewfilepath)
        {
            $content = self::_executeScript($viewfilepath, $request, $response);

            $oReplaceCB = new \JsGreenTeaPHPFramework\ModelAndView\_ContentVariableReplaceCallback($oCore, $request->getAttributes(), $this->m_locale);
            $oReplaceCB->oCore = &$oCore;
            $oReplaceCB->attributes = &$request->getAttributes();
            $content = preg_replace_callback('/[\$#]{([^}]+)}/', array($oReplaceCB, "cbreplace"), $content);
            echo $content;
        }else if($response->getStatus() >= 400)
        {
            echo "ERROR : ".HttpStatus::getString($response->getStatus());
        }
    }
};

namespace JsGreenTeaPHPFramework\ModelAndView;

class _ContentVariableReplaceCallback
{
    public $oCore;
    public $oAutoWiring;
    public $oResourceManager;
    public $oMessageSource;
    public $attributes;
    public $locale;

    public function __construct(&$oCore, &$attributes, &$locale)
    {
        $this->oCore = $oCore;
        $this->oAutoWiring = $oCore->_getAutoWiring();
        $this->oResourceManager = $this->oAutoWiring->getObject('resourceManager');
        $this->attributes = $attributes;
        $this->locale = $locale;
    }

    public function cbreplace($matches)
    {
        $type = substr($matches[0], 0, 1);
        if($type == '$')
            return $this->attributes[$matches[1]];
        else if($type == '#')
        {
            $value = $this->oResourceManager->getResString($matches[1]);
            if(!$value)
            {
                if(!$this->oMessageSource)
                {
                    $this->oMessageSource = $this->oAutoWiring->getObject('messageSource');
                }
                if($this->oMessageSource)
                {
                    $value = $this->oMessageSource->getMessage($matches[1], NULL, $this->locale);
                }
            }
            return $value;
        }
    }
}
