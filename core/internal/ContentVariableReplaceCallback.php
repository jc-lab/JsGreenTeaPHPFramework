<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   ContentVariableReplaceCallback
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2018/02/22
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core\internal;

class ContentVariableReplaceCallback
{
    public $oCore;
    public $oResourceManager;
    public $attributes;
    public $locale;
    public $replacecount = 0;
    public $attributeCallbacks;
    public $oView;

    public function __construct(&$oCore, &$locale, &$oView, &$attributes, &$attributeCallbacks, $oResourceManager = null)
    {
        $this->oCore = $oCore;
        if(!$oResourceManager) {
            $oAutoWiring = $oCore->_getAutoWiring();
            $this->oResourceManager = $oAutoWiring->getObject('resourceManager');
        }else{
            $this->oResourceManager = $oResourceManager;
        }
        $this->attributes = $attributes;
        $this->attributeCallbacks = $attributeCallbacks;
        $this->locale = $locale;
        $this->oView = $oView;
    }

    private function realreplace($matches)
    {
        $this->replacecount++;
        $type = substr($matches[0], 0, 1);
        $result = NULL;
        if($this->attributeCallbacks) {
            foreach ($this->attributeCallbacks as &$item) {
                $result = call_user_func($item[0], $type, $matches[1], $item[1]);
                if ($result)
                    return $result;
            }
        }
        $result = $this->oCore->resolveResOnce($matches[0], $this->locale, $this->oView);
        if (!$result && $this->attributes) {
            $result = @$this->attributes[$matches[1]];
        }
        return $result;
    }

    public function cbreplace($matches)
    {
        $i=0;
        $content = $this->realreplace($matches);
        do {
            $oReplaceCB = new ContentVariableReplaceCallback($this->oCore, $this->m_locale, $this->oView, $this->attributes, $this->attributeCallbacks);
            $content = preg_replace_callback('/[\$#]{([^}]+)}/', array($oReplaceCB, "cbreplace"), $content);
            $i++;
            if($i > 100)
                return NULL;
        }while($oReplaceCB->replacecount > 0);
        return $content;
    }

    public static function replace($content, &$oCore, &$locale, &$oView, &$attributes, &$attributeCallbacks) {
        $i=0;
        do {
            $oReplaceCB = new ContentVariableReplaceCallback($oCore, $locale, $oView, $attributes, $attributeCallbacks);
            $content = preg_replace_callback('/[\$#]{([^}]+)}/', array($oReplaceCB, "cbreplace"), $content);
            $i++;
            if($i > 100)
                return NULL;
        }while($oReplaceCB->replacecount > 0);
        return $content;
    }
}
