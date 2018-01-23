<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   CookieLocaleResolver
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/18
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\i18n;

class CookieLocaleResolver implements LocaleResolver
{
    private $m_cookieName;
    private $m_cookieDomain;
    private $m_cookiePath;
    private $m_cookieMaxAge;
    private $m_defaultLocale;
    private $m_locale = NULL;

    public function setCookieName($value)
    {
        $this->m_cookieName = $value;
    }
    public function setCookieDomain($value)
    {
        $this->m_cookieDomain = $value;
    }
    public function setCookiePath($value)
    {
        $this->m_cookiePath = $value;
    }
    public function setCookieMaxAge($value)
    {
        $this->m_cookieMaxAge = $value;
    }
    public function setDefaultLocale($value)
    {
        $this->m_defaultLocale = $value;
    }

    public function resolveLocale($request)
    {
        if(!$this->m_locale)
        {
            $this->m_locale = new \JsGreenTeaPHPFramework\core\Locale();
            if(isset($_COOKIE[$this->m_cookieName]) && strlen($_COOKIE[$this->m_cookieName]) > 0)
            {
                $this->m_locale->setLocale($_COOKIE[$this->m_cookieName]);
            }else{
                $this->m_locale->setLocale($this->m_defaultLocale);
            }
        }
        return $this->m_locale;
    }

    public function setLocale($request, $response, $locale)
    {
        if(gettype($locale) == "string")
        {
            if(!$this->m_locale) {
                $this->m_locale = new \JsGreenTeaPHPFramework\core\Locale();
            }
            $this->m_locale->setLocale($locale);
        }else{
            $this->m_locale = $locale;
        }
        $value = $this->m_locale->language.'-'.$this->m_locale->country;
        if($this->m_cookieMaxAge <= 0)
            $expire = 0;
        else
            $expire = time() + $this->m_cookieMaxAge;
        setcookie($this->m_cookieName, $value, $expire, $this->m_cookiePath, $this->m_cookieDomain);
    }
}