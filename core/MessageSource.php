<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   MessageSource
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/14
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

class MessageSource extends FrameworkObject
{
    private $m_defaultLocale;
    private $m_messageXmls;

    private $m_messagesDirs = array();

    private $m_cache = array();

    public function __construct()
    {
        $this->m_messageXmls = array();
    }

    public function setDefaultLocale($defaultLocale)
    {
        $this->m_defaultLocale = new Locale($defaultLocale);
    }

    public function addMessagesDir($dirName)
    {
        if(!in_array($dirName, $this->m_messagesDirs))
        {
            $this->m_messagesDirs[] = $dirName;
        }
    }

    public function getMessage($name, $parameters, $locale, $bOnlyPublic = false)
    {
        $name = strtolower($name);
        $oFrameworkCache = self::getFrameworkCache();
        if(isset($this->m_cache[$name]))
        {
            $item = &$this->m_cache[$name];
            if($bOnlyPublic && (($item[2] & 1) == 0))
                return NULL;
            $oReplaceCB = new \JsGreenTeaPHPFramework\MessageSource\_ContentVariableReplaceCallback($parameters);
            $content = preg_replace_callback('/{([^}]+)}/', array($oReplaceCB, "cbreplace"), $item[0]);
            return $content;
        }
        $workdir = self::getCore()->getWorkDir();
        for($i=count($this->m_messagesDirs); $i >= 0; $i--)
        {
            if($i == 0) {
                $filename = 'res/messages_' . $locale->language . '.xml';
                $filepath = $workdir.'/'.$filename;
                $filestat = @stat($filepath);
                if(!$filestat)
                {
                    $filename = 'res/messages.xml';
                    $filepath = $workdir.'/'.$filename;
                    $filestat = @stat($filepath);
                    if(!$filestat)
                    {
                        continue;
                    }
                }
            }else{
                $dirname = $this->m_messagesDirs[$i - 1];
                $filename = 'res/'.$dirname.'/messages_' . $locale->language . '.xml';
                $filepath = $workdir.'/'.$filename;
                $filestat = @stat($filepath);
                if(!$filestat)
                {
                    $filename = 'res/'.$dirname.'/messages.xml';
                    $filepath = $workdir.'/'.$filename;
                    $filestat = @stat($filepath);
                    if(!$filestat)
                    {
                        continue;
                    }
                }
            }

            $filenamehash = substr(md5($filename), 0, 16);

            $cached = $oFrameworkCache->getEx('msgsrc:'.$filenamehash.':'.$name);
            if($cached)
            {
                if($filestat['mtime'] == $cached[1])
                {
                    if($bOnlyPublic && (($cached[2] & 1) == 0))
                        return NULL;
                    $oReplaceCB = new \JsGreenTeaPHPFramework\MessageSource\_ContentVariableReplaceCallback($parameters);
                    $content = preg_replace_callback('/{([^}]+)}/', array($oReplaceCB, "cbreplace"), $cached[0]);
                    return $content;
                }
            }

            $xmlObject = simplexml_load_file($filepath);
            if(isset($xmlObject->string))
            {
                $items = array();
                foreach($xmlObject->string as $item)
                {
                    $attrs = $item->attributes();
                    $attr_name = strtolower($attrs['name']->__toString());
                    $attr_public = false;
                    if(isset($attrs['public']))
                    {
                        $strattr_public = strtolower($attrs['public']->__toString());
                        if($strattr_public == 'true' || $strattr_public == '1')
                            $attr_public = true;
                    }
                    if(!array_key_exists($attr_name, $this->m_cache)) {
                        $cachekey = 'msgsrc:'.$filenamehash.':'.$attr_name;
                        $items[$cachekey] = array($item->__toString(), $filestat['mtime'], $attr_public ? 1 : 0);
                        $this->m_cache[$attr_name] = $items[$cachekey];
                    }
                }
                if(count($items) > 0) {
                    $oFrameworkCache->setManyEx($items);
                }
            }
        }
        if(isset($this->m_cache[$name]))
        {
            $item = &$this->m_cache[$name];
            if($bOnlyPublic && (($item[2] & 1) == 0))
                return NULL;
            $oReplaceCB = new \JsGreenTeaPHPFramework\MessageSource\_ContentVariableReplaceCallback($parameters);
            $content = preg_replace_callback('/{([^}]+)}/', array($oReplaceCB, "cbreplace"), $item[0]);
            return $content;
        }
        return NULL;
    }
}

namespace JsGreenTeaPHPFramework\MessageSource;

class _ContentVariableReplaceCallback
{
    private $m_parameters;

    public function __construct($parameters)
    {
        $this->m_parameters = $parameters;
    }

    public function cbreplace($matches)
    {
        $index = intval($matches[1]);
        return $this->m_parameters[$index - 1];
    }
};
