<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   FrameworkErrno
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/30
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

class FrameworkErrno
{
    const ENO_SUCCESS = 1;
    const ENO_UNKNOWN = 0;
    const ENO_USERNOTEXISTS = -10001;
    const ENO_USERWRONGPASSWORD = -10002;
};
