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
    private $m_model_attributes = array();
    private $m_locale = NULL;
    private $m_model_bindparams = array();

    private $m_attributeCallbacks = array();

    private $m_pageContext = null;

    public function __construct($viewName = NULL)
    {
        $this->m_pageContext = array(
            'request' => array(
                'viewName' => $viewName,
                'viewPath' => $viewName,
            )
        );
    }

    public function setLocale($locale)
    {
        $this->m_locale = $locale;
    }

    public function setViewName($viewName)
    {
        $this->m_pageContext['request']['viewName'] = $viewName;
        $this->m_pageContext['request']['viewPath'] = $viewName;
    }

    public function setViewPath($viewPath)
    {
        $this->m_pageContext['request']['viewPath'] = $viewPath;
    }

    public function getViewName()
    {
        return $this->m_pageContext['request']['viewName'];
    }

    public function getViewPath()
    {
        return $this->m_pageContext['request']['viewPath'];
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

    public static function _executeScript($__viewfilepath, &$request, &$response, &$pageContext, &$__modelbindparams)
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
        if(strpos($this->m_pageContext['request']['viewPath'], $conststr_redirect) === 0)
        {
            $redirectUrl = substr($this->m_pageContext['request']['viewPath'], strlen($conststr_redirect));
            header("Location: ".$redirectUrl);
            return ;
        }
        if($this->m_pageContext['request']['viewPath']) {
            $viewfilepath = $oCore->getWorkDir() . '/view/' . $this->m_pageContext['request']['viewPath'] . '.php';
            $viewfilestat = @stat($viewfilepath);
            if (!$viewfilestat) {
                $response->setStatus(HttpStatus::HTTP_NOT_IMPLEMENTED);
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
            $gttlBlock = new \JsGreenTeaPHPFramework\core\internal\GTTLProcessBlock(NULL);
            $content = self::_executeScript($viewfilepath, $request, $response, $this->m_pageContext, $this->m_model_bindparams);
            $content = \JsGreenTeaPHPFramework\core\internal\GTTLProcessor::process($gttlBlock, $content);
            $content = \JsGreenTeaPHPFramework\core\internal\ContentVariableReplaceCallback::replace($content, $oCore, $this->m_locale, $this, $request->getAttributes(), $this->m_attributeCallbacks);
            echo $content;
        }else if($response->getStatus() >= 400)
        {
            throw new HttpResponseException($response);
        }
    }
};
