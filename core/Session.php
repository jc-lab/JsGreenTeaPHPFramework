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

abstract class Session
{
    /**
     * Get session id
     *
     * @return string
     */
    public abstract function getId();

    /**
     * @deprecated
     * @return string
     */
    public abstract function getSessionId();

    public abstract function setAttribute($key, $value);
    public abstract function getAttribute($key);
    public abstract function destroy();

    /**
     * @return bool if the session has destoried, return false, otherwise true
     */
    public abstract function isValid();

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
