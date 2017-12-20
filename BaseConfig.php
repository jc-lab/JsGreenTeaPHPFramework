<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   IConfig
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/14
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */


namespace JsGreenTeaPHPFramework;

class BaseConfig
{
    private $m_website_name;
    private $m_configs;

    public $frameworksql_host = "localhost";
    public $frameworksql_port = 3306;
    public $frameworksql_username = "";
    public $frameworksql_password = "";
    public $frameworksql_socket = "";
    public $frameworksql_dbname = "";
    public $frameworksql_tableprefix = "";

    public $sitesql_host = "localhost";
    public $sitesql_port = 3306;
    public $sitesql_username = "";
    public $sitesql_password = "";
    public $sitesql_socket = "";
    public $sitesql_dbname = "";
    public $sitesql_tableprefix = "";

    public $frameworkredis_host = "localhost";
    public $frameworkredis_port = 6379;
    public $frameworkredis_password = NULL;
    public $frameworkredis_dbidx = 0;
    public $frameworkredis_keyprefix = "";

    public $session_timeout = 3600;
    public $session_cookiename = "JGTFSESSION";

    public function __construct($websitename)
    {
        $this->m_website_name = $websitename;
        $this->m_configs = array(
            'routes' => array()
        );
    }

    public function init()
    {
        // User
    }

    public function &_friend_core()
    {
        return $this->m_configs;
    }

    protected function setRoute($path, $controllerName)
    {
        $this->m_configs['routes'][$path] = $controllerName;
    }
};