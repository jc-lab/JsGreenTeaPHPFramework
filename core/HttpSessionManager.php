<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   HttpSessionManager
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2019/01/05
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

class HttpSessionManager extends FrameworkObject
{
    private $m_settings_maxAge = 0;
    private $m_settings_path = "";
    private $m_settings_domain = "";
    private $m_settings_secure = false;

    private $m_settings_creationPolicy = false;

    public function setSessionCookie($sessionId, $expire = FALSE)
    {
        if($expire === NULL)
        {
            $expire = 0;
            if($this->m_settings_maxAge > 0)
                $expire = time() + $this->m_settings_maxAge;
        }
        setcookie($this->m_oCore->getConfig()->session_cookiename, $sessionId, $expire, $this->m_settings_path, $this->m_settings_domain, $this->m_settings_secure, true);
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
        if (strcmp(strtolower($value), "false") == 0)
            $this->m_settings_secure = false;
        else if (strcmp($value, "0") == 0)
            $this->m_settings_secure = false;
        else
            $this->m_settings_secure = true;
    }

    public function setCreationPolicy($value) {
        $this->m_settings_creationPolicy = $value;
    }

    public function getCreationPolicy() {
        return $this->m_settings_creationPolicy;
    }
}
