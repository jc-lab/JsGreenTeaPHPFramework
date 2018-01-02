<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   Locale
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/18
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

class Locale
{
    // ISO-639 alpha-2 or alpha-3 language code.  You can find a full list of valid language codes in the IANA Language Subtag Registry (search for "Type: language"). The language field is case insensitive, but Locale always canonicalizes to lower case.
    public $language;

    // ISO 3166 alpha-2 country code or UN M.49 numeric-3 area code. You can find a full list of valid country and region codes in the IANA Language Subtag Registry (search for "Type: region"). The country (region) field is case insensitive, but Locale always canonicalizes to upper case.
    public $country;

    public function __construct($input = NULL)
    {
        if($input)
        {
            $this->setLocale($input);
        }
    }

    public function setLocale($input)
    {
        if(gettype($input) == "string")
        {
            $arr = explode('-', $input);
            $num = count($arr);
            $this->country = NULL;
            $this->language = NULL;
            switch($num)
            {
                case 2:
                    $this->country = $arr[1];
                case 1:
                    $this->language = $arr[0];
            }
        }else{
            $this->language = $input->language;
            $this->country = $input->country;
        }
    }
};
