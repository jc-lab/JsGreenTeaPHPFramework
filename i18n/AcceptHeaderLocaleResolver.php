<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   AcceptHeaderLocaleResolver
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/18
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\i18n;

class AcceptHeaderLocaleResolver implements LocaleResolver
{
    public $availableLocales;

    public function resolveLocale($request)
    {
        $tmpLocaleItems = array();
        $arr = explode(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
        foreach($arr as &$item)
        {
            $subarr = explode(';', $item);
            $subarrLang = explode('-', $subarr[0]);
            if(count($subarr) > 1)
            {
                $tmp_quality = intval(substr($subarr[1], strpos($subarr[1], 'q=') + 2));
            }else{
                $tmp_quality = 1.0;
            }

            $tmp_language = $subarrLang[0];
            if(count($subarrLang) > 1)
            {
                $tmp_country = $subarrLang[1];
            }else{
                $tmp_country = NULL;
            }

            $tmpLocaleItems[] = array(
                'language' => $tmp_language,
                'country' => $tmp_country,
                'quality' => $tmp_quality,
            );
        }
        usort($tmpLocaleItems, create_function('$a,$b',
            'if ($a[\'quality\'] == $b[\'quality\']) {
                return 0;
            }
            return ($a[\'quality\'] < $b[\'quality\']) ? -1 : 1;'));
        $this->availableLocales = $tmpLocaleItems;

        $locale = new \JsGreenTeaPHPFramework\core\Locale();
        if(count($tmpLocaleItems > 0))
        {
            $locale->language = $tmpLocaleItems[0]['language'];
            $locale->country = $tmpLocaleItems[0]['country'];
        }
        return $locale;
    }

    public function setLocale($request, $response, $locale)
    {
        // Nothing
    }

};
