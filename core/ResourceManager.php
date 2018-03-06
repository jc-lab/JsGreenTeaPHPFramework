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

namespace JsGreenTeaPHPFramework\core;

class ResourceManager
{
    private $m_oCore;
    private $m_inited = false;
    private $m_common;
    private $m_common_strings;
    private $m_common_settings;

    private $m_receiverHandlers = array();

    public function __construct($oCore)
    {
        $this->m_oCore = $oCore;
    }

    public function addReceiver($cbfunc, $cbparam = null)
    {
        $this->m_receiverHandlers[] = array($cbfunc, $cbparam);
        if($this->m_inited)
        {
            call_user_func($cbfunc, $cbparam);
        }
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
                foreach($this->m_common->setting as $item) {
                    $attrs = $item->attributes();
                    if(isset($attrs['value']))
                        $this->m_common_settings[$attrs['name']->__toString()] = $attrs['value']->__toString();
                    else
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
            if(isset($this->m_common->AutoLoader))
            {
                foreach($this->m_common->AutoLoader as $item) {
                    $attrs = $item->attributes();
                    require_once($attrs['path']);
                }
            }
        }
        $this->m_inited = true;
        foreach($this->m_receiverHandlers as &$item)
        {
            call_user_func($item[0], $item[1]);
        }
    }

    public function &_getCommon()
    {
        return $this->m_common;
    }

    public function getResString($name)
    {
        if(!isset($this->m_common_strings[$name]))
            return NULL;
        return @$this->m_common_strings[$name];
    }

    public function getResSetting($name)
    {
        if(!isset($this->m_common_settings[$name]))
            return NULL;
        return @$this->m_common_settings[$name];
    }

    public function getResSettingBool($name)
    {
        if(!isset($this->m_common_settings[$name]))
            return NULL;
        $value = strtolower($this->m_common_settings[$name]);
        if($value == "true")
            return true;
        else if($value == "1")
            return true;
        return false;
    }
}
