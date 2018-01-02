<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   FastFrameworkCache
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/14
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

require_once("Session.php");
require_once("SqlSession.php");

class FastFrameworkCache
{
    const CACHEID_LATEST_TIDYSESSION_TIME = 1011;

    private $m_oCore;
    private $m_oSession;
    private $m_fwdb;

    public function __construct(&$oCore, &$oSession)
    {
        $this->m_oCore = $oCore;
        $this->m_oSession = $oSession;
    }

    public function _init()
    {
        $this->m_fwdb = &$this->m_oSession->_getFWDB();
    }

    public function getValue($key)
    {
        $dbres = $this->m_fwdb->queryRaw("SELECT `data` FROM `".$this->m_oCore->getFrameworkTable('fastcache')."` WHERE `key`='".$key."'");
        if(!$dbres)
        {
            // Error
            return false;
        }
        if(!($dbrow = $dbres->fetch_array()))
        {
            return NULL;
        }
        return $dbrow['data'];
    }

    public function setValue($key, $value)
    {
        $hexvalue = bin2hex($value);
        $dbres = $this->m_fwdb->queryRaw("INSERT INTO `".$this->m_oCore->getFrameworkTable('fastcache')."` (`key`,`data`) VALUES ('".$key."', X'".$hexvalue."') ON DUPLICATE KEY UPDATE `data`=X'".$hexvalue."'");
        if(!$dbres)
        {
            // Error
            return false;
        }
        return true;
    }
};
