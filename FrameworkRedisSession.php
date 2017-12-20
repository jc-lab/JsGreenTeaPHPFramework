<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   FrameworkRedisSession
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/15
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework;

class FrameworkRedisSession extends RedisSession
{
    public function __construct($object)
    {
        if(is_subclass_of($object, 'JsGreenTeaPHPFramework\RedisSession'))
        {
            $this->m_dbconn = &$object->m_dbconn;
            $this->m_dbconntype = &$object->m_dbconntype;
        }else if(get_class($object) ==  'JsGreenTeaPHPFramework\Core')
        {
            $oConfig = $object->getConfig();
            self::connect($oConfig->frameworkredis_host, $oConfig->frameworkredis_port);
            if($oConfig->frameworkredis_password)
                self::auth($oConfig->frameworkredis_password);
            self::select($oConfig->frameworkredis_dbidx);
        }else{
            throw Exception("Wrong class");
        }
    }
};
