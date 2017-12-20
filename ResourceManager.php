<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   ResourceManager
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/14
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework;

class ResourceManager
{
    private $m_oCore;
    private $m_common;
    private $m_common_strings;
    private $m_common_settings;

    public function __construct($oCore)
    {
        $this->m_oCore = $oCore;
    }

    public function _init()
    {
        $filepath = $this->m_oCore->getWorkDir().'/res/common.xml';
        $this->m_common_strings = array();
        if(file_exists($filepath))
        {
            $this->m_common = simplexml_load_file($filepath);
            if(isset($this->m_common->setting))
            {
                foreach($this->m_common->string as $item) {
                    $attrs = $item->attributes();
                    $this->m_common_settings[$attrs['name']->__toString()] = $item->__toString();
                }
            }
            if(isset($this->m_common->string))
            {
                foreach($this->m_common->string as $item) {
                    $attrs = $item->attributes();
                    $this->m_common_strings[$attrs['name']->__toString()] = $item->__toString();
                }
            }
        }
    }

    public function &_getCommon()
    {
        return $this->m_common;
    }

    public function getResString($name)
    {
        return @$this->m_common_strings[$name];
    }

    public function getResSetting($name)
    {
        return @$this->m_common_settings[$name];
    }
}
