<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   Session
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/14
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

require_once("SqlSession.php");

class Session
{
    private $m_oCore;
    private $m_oConfig;
    private $m_session_id;
    private $m_session_idhex;

    private $m_remoteip;

    private $m_cachedValues;

    private $m_settings_maxAge = 0;
    private $m_settings_path = "";
    private $m_settings_domain = "";
    private $m_settings_secure = false;

    private $m_output_setcookie = false;

    public function __construct($oCore, $oConfig)
    {
        $this->m_oCore = $oCore;
        $this->m_oConfig = $oConfig;
        $this->m_cachedValues = array();
    }

    public function init()
    {
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

        if(isset($_COOKIE[$this->m_oConfig->session_cookiename]))
        {
            $this->m_session_id = hex2bin($_COOKIE[$this->m_oConfig->session_cookiename]);
            $this->m_session_idhex = bin2hex($this->m_session_id);
        }else{
            $temp = openssl_random_pseudo_bytes(16);
            $this->m_session_id = $temp;
            $this->m_session_idhex = bin2hex($this->m_session_id);
            $expire = 0;
            if($this->m_settings_maxAge > 0)
                $expire = time() + $this->m_settings_maxAge;
            $this->setCookie($expire);
        }

        $sqlSession = $this->m_oCore->_getFrameworkSqlSession();

        if(isset($_SERVER['HTTP_USER_AGENT']))
            $useragent = $_SERVER['HTTP_USER_AGENT'];
        else
            $useragent = NULL;

        $this->attachSessionId(NULL, $useragent, $remoteip);
    }

    public function setCookie($expire = FALSE)
    {
        $this->m_output_setcookie = true;
        if($expire === NULL)
        {
            $expire = 0;
            if($this->m_settings_maxAge > 0)
                $expire = time() + $this->m_settings_maxAge;
        }
        $this->m_output_setcookieexpire = $expire;
    }

    public function attachSessionId($sessionid, $useragent = NULL, $remoteip = NULL, $useOnlyExists = false)
    {
        $sqlSession = $this->m_oCore->_getFrameworkSqlSession();

        $this->m_remoteip = $remoteip;

        if($sessionid) {
            $this->m_session_id = $sessionid;
            $this->m_session_idhex = bin2hex($sessionid);
            $this->setCookie(NULL);
        }

        // Query and Update
        $dbres = $sqlSession->queryRaw("SELECT * FROM `".$this->m_oCore->_getFrameworkSqlTable('sessions')."` WHERE `sid`=X'".$this->m_session_idhex."'", NULL, true);
        if(!$useOnlyExists) {
            for($retry = 0; $retry < 2; $retry++) {
                $dbres_update = $sqlSession->queryRaw(
                    "INSERT INTO `" . $this->m_oCore->_getFrameworkSqlTable('sessions') . "` " .
                    "(`sid`,`created_time`,`created_ip`,`created_ua`,`latest_time`,`latest_ip`) VALUES (?, UTC_TIMESTAMP(), ?, ?, UTC_TIMESTAMP(), ?) " .
                    "ON DUPLICATE KEY UPDATE `latest_time`=UTC_TIMESTAMP(), `latest_ip`=?",
                    array(
                        $this->m_session_id, $remoteip, $useragent, $remoteip, $remoteip
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
        }
        $dbrow = $dbres->fetch_array();
        $dbres->close();
        if($dbrow)
        {
            // Existing
            $this->m_session_loginidx = $dbrow['logon_idx'];
            return true;
        }
        if($useOnlyExists)
            return false;
        else
            return true;
    }

    public function getClientAddr() {
        return $this->m_remoteip;
    }

    public function setCookieMaxAge($value)
    {
        $this->m_settings_maxAge = intval($value);
    }
    public function setCookiePath($value)
    {
        $this->m_settings_path = $value;
    }
    public function setCookieDomain($value)
    {
        $this->m_settings_domain = $value;
    }
    public function setCookieSecure($value)
    {
        if(strcmp(strtolower($value), "false") == 0)
            $this->m_settings_secure = false;
        else if(strcmp($value, "0") == 0)
            $this->m_settings_secure = false;
        else
            $this->m_settings_secure = true;
    }

    public function getSessionId()
    {
        return $this->m_session_idhex;
    }

    public function _schedule_tidySession()
    {
        $oFastCache = $this->m_oCore->_getFastCache();
        $needtidy = false;
        $bindata = $oFastCache->getValue(FastFrameworkCache::CACHEID_LATEST_TIDYSESSION_TIME);
        if($bindata === false)
        {
            return false;
        }else if($bindata === NULL)
        {
            $needtidy = true;
        }else{
            $data = unpack('P1', $bindata);
            $difftime = time() - $data[1];
        }
        if($needtidy)
        {
            $droptime = gmdate('Y-m-d H:i:s', time() - $this->m_oConfig->session_timeout);
            $dbres = $this->m_fwdb->queryRaw("SELECT * FROM `".$this->m_oCore->getFrameworkTable('sessions')."` WHERE `latest_time` < '".$droptime."'");
            $dbres->close();
            $bindata = pack('P', time());
            $oFastCache->setValue(FastFrameworkCache::CACHEID_LATEST_TIDYSESSION_TIME, $bindata);
        }
    }

    public function setAttribute($key, $value)
    {
        $frameworkCache = $this->m_oCore->_getFrameworkCache();
        $frameworkCache->setEx('session:'.$this->m_session_idhex.':'.$key, $value);
        $this->m_cachedValues[$key] = $value;
    }

    public function getAttribute($key)
    {
        if(isset($this->m_cachedValues[$key]))
        {
            return $this->m_cachedValues[$key];
        }else {
            $frameworkCache = $this->m_oCore->_getFrameworkCache();
            $value = $frameworkCache->getEx('session:' . $this->m_session_idhex . ':' . $key);
            if(is_array($value))
            {
                $this->m_cachedValues[$key] = $value[0];
                return $value[0];
            }else{
                return NULL;
            }
        }
    }

    public function destroy()
    {
        $frameworkCache = $this->m_oCore->_getFrameworkCache();
        $frameworkCache->del('session:'.$this->m_session_idhex.':*');
        $this->m_cachedValues = array();
    }

    public function onBeginResponse()
    {
        if($this->m_output_setcookie) {
            setcookie($this->m_oConfig->session_cookiename, $this->m_session_idhex, $this->m_output_setcookieexpire, $this->m_settings_path, $this->m_settings_domain, $this->m_settings_secure, false);
        }
    }

    public static function get($name)
    {
        global $request;
        if($request)
        {
            $session = $request->getSession();
            return $session->getAttribute($name);
        }
        return NULL;
    }
};
