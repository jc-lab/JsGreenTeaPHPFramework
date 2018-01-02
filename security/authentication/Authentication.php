<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   Authentication
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/28
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\security\authentication;

class Authentication
{
    protected $m_credentials = NULL;
    protected $m_principal = NULL;

    protected $m_authenticated = false;
    protected $m_userDetails = NULL;

    public function getAuthorities()
    {
        if(!$this->m_userDetails)
            return NULL;
        return $this->m_userDetails->getAuthorities();
    }

    public function getPrincipal()
    {
        return $this->m_principal;
    }

    public function getCredentials()
    {
        return $this->m_credentials;
    }

    public function getDetails($user)
    {
        return $this->m_userDetails;
    }

    public function isAuthenticated()
    {
        return $this->m_authenticated;
    }

    public function setAuthenticated($value)
    {
        $this->m_authenticated = $value;
    }

    public function setDetails($userDetails)
    {
        $this->m_userDetails = $userDetails;
    }
}