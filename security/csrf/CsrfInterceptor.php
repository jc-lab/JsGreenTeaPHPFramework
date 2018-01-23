<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   CsrfInterceptor
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/28
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\security\csrf;

class CsrfInterceptor extends \JsGreenTeaPHPFramework\core\HandlerInterceptor
{
    private $m_csrfManager;
    private $m_currentToken = NULL;
    private $m_methodIntercept = array(
        "GET" => 0,
        "POST" => 0
    );

    public function setCsrfManager(&$csrfManager)
    {
        $this->m_csrfManager = $csrfManager;
    }

    public function preHandle(&$request, &$response, $rev)
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD']);
        if(isset($this->m_methodIntercept[$method]))
        {
            $value = $this->m_methodIntercept[$method];
            if($value == 0)
            {
                $oResourceManager = self::getResourceManager();
                if($oResourceManager->getResSettingsBool("security:csrf:intercept_method_".$method))
                {
                    $value = 1;
                }else{
                    $value = -1;
                }
                $this->m_methodIntercept[$method] = $value;
            }
            if($value == 1)
            {
                return $this->m_csrfManager->validCsrf($request);
            }
        }
        return true;
    }

    public function postHandle(&$request, &$response, $rev, &$oModelAndView)
    {
        $oModelAndView->_addAttributeCallback(array(&$this, "_attributeReplaceCB"), array(&$request));
        return true;
    }

    public function _attributeReplaceCB($type, $name, $param)
    {
        $request = &$param[0];
        $session = $request->getSession();
        if($type == '$')
        {
            $name = strtolower($name);
            if($name == '_csrf.paramname')
            {
                return $this->m_csrfManager->getParamName();
            }else if($name == '_csrf.headername')
            {
                return $this->m_csrfManager->getHeaderName();
            }else if($name == '_csrf.token')
            {
                if(!$this->m_currentToken)
                    $this->m_currentToken = $this->m_csrfManager->getNewToken($session);
                return $this->m_currentToken;
            }
        }
        return NULL;
    }
};
