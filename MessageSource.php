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

namespace JsGreenTeaPHPFramework;

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

    public function getMessage($name, $parameters, $locale)
    {
        $oFrameworkCache = self::getFrameworkCache();
        if(isset($this->m_cache[$name]))
        {
            $oReplaceCB = new \JsGreenTeaPHPFramework\MessageSource\_ContentVariableReplaceCallback($parameters);
            $content = preg_replace_callback('/{([^}]+)}/', array($oReplaceCB, "cbreplace"), $this->m_cache[$name]);
            return $content;
        }
        $workdir = self::getCore()->getWorkDir();
        for($i=count($this->m_messagesDirs); $i >= 0; $i--)
        {
            if($i == 0) {
                $filename = 'res/messages_' . $locale->language . '.xml';
                $filepath = $workdir.'/'.$filename;
                $filestat = stat($filepath);
                if(!$filestat)
                {
                    $filename = 'res/messages.xml';
                    $filepath = $workdir.'/'.$filename;
                    $filestat = stat($filepath);
                    if(!$filestat)
                    {
                        continue;
                    }
                }
            }else{
                $dirname = $this->m_messagesDirs[$i - 1];
                $filename = 'res/'.$dirname.'/messages_' . $locale->language . '.xml';
                $filepath = $workdir.'/'.$filename;
                $filestat = stat($filepath);
                if(!$filestat)
                {
                    $filename = 'res/'.$dirname.'/messages.xml';
                    $filepath = $workdir.'/'.$filename;
                    $filestat = stat($filepath);
                    if(!$filestat)
                    {
                        continue;
                    }
                }
            }

            $filenamehash = substr(md5($filename), 0, 8);

            $cached = $oFrameworkCache->getEx('msgsrc:'.$filenamehash.':'.$name);
            if($cached)
            {
                if($filestat['mtime'] == $cached[1])
                {
                    return $cached[0];
                }
            }

            $xmlObject = simplexml_load_file($filepath);
            if(isset($xmlObject->string))
            {
                $items = array();
                foreach($xmlObject->string as $item)
                {
                    $attrs = $item->attributes();
                    $name = $attrs['name']->__toString();
                    if(!array_key_exists($name, $this->m_cache)) {
                        $cachekey = 'msgsrc:'.$filenamehash.':'.$name;
                        $items[$cachekey] = array($item->__toString(), $filestat['mtime']);
                        $this->m_cache[$name] = $item->__toString();
                    }
                }
                if(count($items) > 0) {
                    $oFrameworkCache->setManyEx($items);
                }
            }
        }
        if(isset($this->m_cache[$name]))
        {
            $oReplaceCB = new \JsGreenTeaPHPFramework\MessageSource\_ContentVariableReplaceCallback($parameters);
            $content = preg_replace_callback('/{([^}]+)}/', array($oReplaceCB, "cbreplace"), $this->m_cache[$name]);
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
