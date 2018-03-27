<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   GTTLProcessBlock
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2018/03/26
 * @copyright Copyright (C) 2018 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core\internal;

require_once(__DIR__.'/../../libraries/simple_html_dom.php');

class GTTLProcessor
{
    public static function process($rootBlock, $html)
    {
        $htmldoc = str_get_html($html, true, true, DEFAULT_TARGET_CHARSET, false);
        $pos = 0;
        do {
            $matches = NULL;
            preg_match_all('/<c:(\w+)[^>]*>/', $htmldoc->innertext, $matches, PREG_OFFSET_CAPTURE, $pos);

            if((count($matches) > 0) && (count($matches[0]) > 0))
            {
                $tagname = 'c:'.$matches[1][0][0];
                $pos = $matches[0][0][1];
                $rootnode = $htmldoc->find($tagname)[0];
                $rootBlock->process($rootnode);
            }
            break;
        }while(count($matches) > 1);
        return $htmldoc->save();
    }
}
