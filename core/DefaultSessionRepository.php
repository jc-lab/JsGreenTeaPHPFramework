<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   DefaultSessionRepository
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2019/01/05
 * @copyright Copyright (C) 2018 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

class DefaultSessionAttributeMap implements \ArrayAccess {
    private $m_oCore;

    private $cachedMap = array();
    private $m_session_id;

    public function __construct($oCore, $sessionId)
    {
        $this->m_oCore = $oCore;
        $this->m_session_id = $sessionId;
    }

    public function offsetExists ( $offset ) {
        if(isset($this->cachedMap[$offset])) {
            return true;
        }else{
            $frameworkCache = $this->m_oCore->_getFrameworkCache();
            $value = $frameworkCache->getEx('session:' . $this->m_session_id . ':' . $offset);
            if(is_array($value))
            {
                $this->m_cachedValues[$offset] = $value[0];
                return true;
            }
        }
        return false;
    }
    public function offsetGet ( $offset ) {
        if(isset($this->cachedMap[$offset])) {
            return $this->cachedMap[$offset];
        }else{
            $frameworkCache = $this->m_oCore->_getFrameworkCache();
            $value = $frameworkCache->getEx('session:' . $this->m_session_id . ':' . $offset);
            if(is_array($value))
            {
                $this->m_cachedValues[$offset] = $value[0];
                return $value[0];
            }
        }
        return NULL;
    }
    public function offsetSet ( $offset , $value ) {
        $this->cachedMap[$offset] = $value;
        $frameworkCache = $this->m_oCore->_getFrameworkCache();
        $frameworkCache->setEx('session:'.$this->m_session_id.':'.$offset, $value);
    }
    public function offsetUnset ( $offset ) {
        unset($this->cachedMap[$offset]);
        $frameworkCache = $this->m_oCore->_getFrameworkCache();
        $frameworkCache->del('session:'.$this->m_session_id.':'.$offset);
    }
}

class DefaultSessionRepository extends FrameworkObject implements SessionRepository
{
    public function refresh($sessionId) {
        if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'])
            $remoteip = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'])
            $remoteip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']) && $_SERVER['HTTP_X_FORWARDED'])
            $remoteip = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']) && $_SERVER['HTTP_FORWARDED_FOR'])
            $remoteip = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']) && $_SERVER['HTTP_FORWARDED'])
            $remoteip = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'])
            $remoteip = $_SERVER['REMOTE_ADDR'];
        else
            $remoteip = '*UNKNOWN';

        if(isset($_SERVER['HTTP_USER_AGENT']))
            $useragent = $_SERVER['HTTP_USER_AGENT'];
        else
            $useragent = NULL;

        $sqlSession = $this->m_oCore->_getFrameworkSqlSession();

        //$this->m_remoteip = $remoteip;
        /*
        if($sessionid) {
            $this->m_session_id = $sessionid;
            $this->m_session_idhex = bin2hex($sessionid);
            $this->setCookie(NULL);
        }
        */

        // Query and Update
        $dbres = $sqlSession->queryRaw("SELECT * FROM `".$this->m_oCore->_getFrameworkSqlTable('sessions')."` WHERE `sid`=X'".$sessionId."'", NULL, true);
        //if(!$useOnlyExists) {
            for($retry = 0; $retry < 2; $retry++) {
                $dbres_update = $sqlSession->queryRaw(
                    "INSERT INTO `" . $this->m_oCore->_getFrameworkSqlTable('sessions') . "` " .
                    "(`sid`,`created_time`,`created_ip`,`created_ua`,`latest_time`,`latest_ip`) VALUES (?, UTC_TIMESTAMP(), ?, ?, UTC_TIMESTAMP(), ?) " .
                    "ON DUPLICATE KEY UPDATE `latest_time`=UTC_TIMESTAMP(), `latest_ip`=?",
                    array(
                        hex2bin($sessionId), $remoteip, $useragent, $remoteip, $remoteip
                    ));
                if ($dbres_update) {
                    $dbres_update->close();
                    break;
                } else {
                    if($sqlSession->errno == 1114)
                    {
                        $tmpdbres = $sqlSession->queryRaw("DELETE FROM `webfw_sessions` TIMESTAMPDIFF(SECOND, `latest_time`, UTC_TIMESTAMP()) > 86400");
                        if($tmpdbres)
                        {
                            $tmpdbres->close();
                        }
                    }
                }
            }
        //}
        $dbrow = $dbres->fetch_array();
        $dbres->close();
        if($dbrow)
        {
            // Existing
            //$this->m_session_loginidx = $dbrow['logon_idx'];
            return true;
        }
        //if($useOnlyExists)
        //    return false;
        //else
        return true;
    }

    public function createSession() {
        $sessionIdBin = openssl_random_pseudo_bytes(16);
        $sessionId = bin2hex($sessionIdBin);
        $attributeMap = new DefaultSessionAttributeMap($this->m_oCore, $sessionId);
        $session = new DefaultSession($this, $sessionId, $attributeMap);
        $this->refresh($sessionId);
        return $session;
    }

    public function save($session) {
        // nothing
    }

    public function findById($sessionId) {
        if(!$sessionId)
            return NULL;
        $attributeMap = new DefaultSessionAttributeMap($this->m_oCore, $sessionId);
        $session = new DefaultSession($this, $sessionId, $attributeMap);
        $this->refresh($sessionId);
        return $session;
    }

    public function deleteById($sessionId) {
        if(!$sessionId)
            return ;
        $frameworkCache = $this->m_oCore->_getFrameworkCache();
        $frameworkCache->del('session:'.$this->m_session_idhex.':*');
        $this->m_cachedValues = array();
    }

}

