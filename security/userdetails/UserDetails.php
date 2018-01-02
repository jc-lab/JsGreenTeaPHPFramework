<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   UserDetails
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/27
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\security\userdetails;

interface UserDetails
{
    // Returns the authorities granted to the user. (Array)
    public function getAuthorities();

    // Returns the password used to authenticate the user.
    public function getPassword();

    // Returns the username used to authenticate the user.
    public function getUsername();

    // Indicates whether the user's account has expired.
    public function isAccountNonExpired();

    // Indicates whether the user is locked or unlocked.
    public function isAccountNonLocked();

    // Indicates whether the user's credentials (password) has expired.
    public function isCredentialsNonExpired();

    // Indicates whether the user is enabled or disabled.
    public function isEnabled();
}
