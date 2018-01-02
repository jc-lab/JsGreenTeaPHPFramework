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

class AuthenticationToken extends Authentication
{
    public function __construct($principal, $credentials, $details)
    {
        $this->m_principal = $principal;
        $this->m_credentials = $credentials;
        $this->m_userDetails = $details;
    }

    public function setPrincipal($value)
    {
        $this->m_principal = $value;
    }

    public function setCredentials($value)
    {
        $this->m_credentials = $value;
    }

    public function setDetails($value)
    {
        $this->m_userDetails = $value;
    }
}