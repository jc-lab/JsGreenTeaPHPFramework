<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   HandlerInterceptor
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/18
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework;

class HandlerInterceptor extends FrameworkObject
{
    public function preHandle(&$request, &$response, $rev){ return true; }
    public function postHandle(&$request, &$response, $rev, &$oModelAndView){ return true; }
}
