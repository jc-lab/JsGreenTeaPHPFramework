<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   AutoLoader
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/30
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework;

class AutoLoader
{
    public function autoload($className)
    {
        $prefix = __NAMESPACE__.'\\';
        if (strpos($className, $prefix) === 0) {
            $subClsName = substr($className, strlen($prefix));
            $subClsArr = explode('\\', $subClsName);
            $clsPath = "";
            foreach ($subClsArr as $item) {
                $clsPath .= '/' . $item;
            }
            $clsPath .= '.php';
            require_once(__DIR__ . $clsPath);
        }
    }

    public static function registerAutoLoader()
    {
        $loader = new AutoLoader();
        spl_autoload_register(array($loader, 'autoload'), true, true);
    }
};

AutoLoader::registerAutoLoader();

