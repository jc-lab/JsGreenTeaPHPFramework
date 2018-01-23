<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   PasswordEncoder
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/28
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\security\authentication\encoding;

class BasePasswordEncoder extends \JsGreenTeaPHPFramework\core\FrameworkObject
{
    public static function getSaltPrefix($algorithm)
    {
        if($algorithm == 'md5')
            return "\x01";
        else if($algorithm == 'bcrypt')
            return "\x2a";
        else if($algorithm == "sha256")
            return "\x05";
        else if($algorithm == "sha512")
            return "\x06";
        else if($algorithm == "pbkdf2")
            return "\x12";
        else
            return NULL;
    }
}