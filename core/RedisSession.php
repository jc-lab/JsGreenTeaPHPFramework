<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   RedisSession
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/15
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

if(!class_exists("Redis", true))
    require_once(__DIR__.'/../libraries/predis/autoload.php');

class RedisSession
{
    protected $m_dbconn = NULL;
    protected $m_dbconntype = 0; // 1 : phpredis / 2 : predis

    public function &getConnection()
    {
        return $this->m_dbconn;
    }

    public function connect($host = "127.0.0.1", $port = 6379, $timeoutsec = 2.5, $rev = NULL, $retry_interval = 100)
    {
        if(class_exists("Redis", true))
        {
            $this->m_dbconn = new \Redis();
            $this->m_dbconn->connect($host, $port, $timeoutsec, $rev, $retry_interval);
            $this->m_dbconntype = 1;
        }else {
            \Predis\Autoloader::register();
            $isunixsock = false;
            if (substr($host, 0, 1) == "/")
                $isunixsock = true;
            if ($isunixsock) {
                $this->m_dbconn = new \Predis\Client([
                    'scheme' => 'unix',
                    'path' => $host
                ]);
            } else {
                $this->m_dbconn = new \Predis\Client([
                    'scheme' => 'tcp',
                    'host' => $host,
                    'port' => $port,
                ]);
            }
            $this->m_dbconntype = 2;
        }
    }

    public function auth($passwd)
    {
        return $this->m_dbconn->auth($passwd);
    }

    public function select($dbidx)
    {
        return $this->m_dbconn->select($dbidx);
    }

    public function close()
    {
        $this->m_dbconn->close();
    }

    public function set($key, $value, $expireResolution = null, $expireTTL = null, $flag = null)
    {
        return $this->m_dbconn->set($key, $value);  //setex($key, $expireTTL, $serializedData); //, $expireResolution, $expireTTL, $flag);
    }

    public function mset($pairs)
    {
        return $this->m_dbconn->mset($pairs);
    }

    public function setex($key, $ttl, $value)
    {
        // $ttl : seconds
        return $this->m_dbconn->setex($key, $ttl, $value);
    }

    public function get($key)
    {
        $serializedData = $this->m_dbconn->get($key);
        return json_decode($serializedData);
    }

    public function hgetall($key)
    {
        return $this->m_dbconn->hgetall($key);
    }

    public function hset($key, $hkey, $hvalue)
    {
        return $this->m_dbconn->hset($key, $hkey, $hvalue);
    }

    public function getset($key, $value)
    {
        $serializedData = $this->m_dbconn->getSet($key, $value);
        return json_decode($serializedData);
    }

    public function exists($key)
    {
        return $this->m_dbconn->exists($key);
    }

    public function scan(&$cursor, $options = null)
    {
        return $this->m_dbconn->scan($cursor, $options);
    }

    public function del($keys)
    {
        return $this->m_dbconn->del($keys);
    }

};
