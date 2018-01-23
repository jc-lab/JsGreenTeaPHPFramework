<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   LocaleChangeInterceptor
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/18
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\i18n;

class LocaleChangeInterceptor extends \JsGreenTeaPHPFramework\core\HandlerInterceptor
{
    private $m_paramName;

    public function preHandle(&$request, &$response, $rev)
    {
        if(isset($_GET[$this->m_paramName]))
        {
            $newLangCode = $_GET[$this->m_paramName];
            $localeResolver = self::getAutoWiredObject("localeResolver");
            $localeResolver->setLocale($request, $response, $newLangCode);
        }
        return true;
    }

    public function setParamName($paramName)
    {
        $this->m_paramName = $paramName;
    }
};
