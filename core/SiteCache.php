<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   SiteCache
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2018/01/05
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

class SiteCache extends FrameworkObject
{
    private $m_oFrameworkCache;

    public function __construct($oFrameworkCache)
    {
        $this->m_oFrameworkCache = $oFrameworkCache;
    }

    public function get($key)
    {
        return $this->m_oFrameworkCache->getRaw('sitecache:'.$key);
    }

    public function set($key, $value)
    {
        return $this->m_oFrameworkCache->setRaw('sitecache:'.$key, $value);
    }

    public function getEx($key)
    {
        return $this->m_oFrameworkCache->getEx('sitecache:'.$key);
    }

    public function setEx($key, &$value, $assist = 0)
    {
        return $this->m_oFrameworkCache->setEx('sitecache:'.$key, $value, $assist);
    }
}