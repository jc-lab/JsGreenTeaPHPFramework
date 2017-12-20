<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   SiteSqlSession
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/15
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework;

class SiteSqlSession extends SqlSession
{
    public function __construct($object)
    {
        if(is_subclass_of($object, 'JsGreenTeaPHPFramework\SqlSession'))
        {
            $this->m_dbconn = &$object->m_dbconn;
            $this->connect_errno = &$object->connect_errno;
            $this->connect_error = &$object->connect_error;
            $this->error = &$object->error;
            $this->errno = &$object->errno;
        }else if(get_class($object) ==  'JsGreenTeaPHPFramework\Core')
        {
            $oConfig = $object->getConfig();
            self::connect($oConfig->sitesql_host, $oConfig->sitesql_username, $oConfig->sitesql_password, $oConfig->sitesql_dbname, $oConfig->sitesql_port, $oConfig->sitesql_socket);
        }else{
            throw Exception("Wrong class");
        }
    }
};
