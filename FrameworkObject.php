<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   FrameworkObject
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/18
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework;

class FrameworkObject
{
    protected $m_oCore;
    private $m_oAutoWiring;

    public function _setCore($oCore)
    {
        $this->m_oCore = $oCore;
        $this->m_oAutoWiring = $oCore->_getAutoWiring();
    }

    public function &getCore()
    {
        return $this->m_oCore;
    }

    public function &getAutoWiredObject($id)
    {
        return $this->m_oAutoWiring->getObject($id);
    }

    public function &getFrameworkCache()
    {
        return $this->m_oCore->_getFrameworkCache();
    }
}