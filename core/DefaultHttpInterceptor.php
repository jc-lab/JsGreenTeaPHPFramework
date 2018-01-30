<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   DefaultHttpInterceptor
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/31
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

class DefaultHttpInterceptor extends HandlerInterceptor
{
    private $m_oResourceManager;

    private $m_httpInterceptors = array();

    public function __construct(&$oResourceManager)
    {
        $this->m_oResourceManager = $oResourceManager;
        $this->m_oResourceManager->addReceiver(array($this, "_onResourceReceive"));
    }

    public function _onResourceReceive($cbparam)
    {
        $oCommon = $this->m_oResourceManager->_getCommon();
        if(isset($oCommon->http))
        {
            $http = $oCommon->http;
            if(isset($http->{'intercept-url'}))
            {
                foreach($http->{'intercept-url'} as $item)
                {
                    $attr = $item->attributes();
                    $this->m_httpInterceptors[] = array(
                        'pattern' => $attr['pattern'],
                        'access' => $attr['access']
                    );
                }
            }
        }
    }

    public function _interceptorOperandProc($type, $name, $params, $cbparam)
    {
        $request = &$cbparam[0];
        $authenticationManager = &$cbparam[1];
        $name = strtolower($name);
        if($name == "permitall")
        {
            return 1;
        }else if($name == "deny"){
            return 0;
        }else if($name == "denyall"){
            return 0;
        }

        $pageSession = &$request->getPageSession();
        if(isset($pageSession['userdetails']))
        {
            $userdetails = &$pageSession['userdetails'];
        }else{
            $userdetails = NULL;
        }

        if($name == 'hasrole')
        {
            if(!$userdetails)
                return false;
            $roles = $userdetails->getAuthorities();
            return in_array($params[0], $roles) ? 1 : 0;
        }

        return 0;
    }
    
    public function preHandle(&$request, &$response, $rev)
    {
        $urlpage = $request->getUrlPath();
        $matchedIntercept = NULL;
        foreach($this->m_httpInterceptors as $interceptItem)
        {
            $filteredpattern = preg_replace("/([-\\[\\]\\/{}()+?.,\\\\^$|#\\s])/", "\\\\$0", $interceptItem['pattern']);
            $filteredpattern = '/^'.preg_replace_callback ('(\*\*|\*)', create_function('$matches',
                'if($matches[0] == \'**\')
                {
                    return "(.*)";
                }else if($matches[0] == \'*\')
                {
                    return "([^\\\\/]*)";
                }'
                ), $filteredpattern).'$/';
            $matches = NULL;
            $findrst = preg_match($filteredpattern, $urlpage, $matches);
            if($findrst) {
                $matchedIntercept = $interceptItem;
                break;
            }
        }
        if($matchedIntercept)
        {
            $oParser = new \JsGreenTeaPHPFramework\util\OperatorComputeObject();
            $oParser->setOperandCallback(array($this, '_interceptorOperandProc'), array(&$request, self::getCore()->_getFrameworkInternalObject('authenticationManager')));
            $isPermited = $oParser->parse($matchedIntercept['access']->__toString());
            if(!$isPermited)
                throw new AccessDeniedException();
            return $isPermited ? true : false;
        }
        return true;
    }
}
