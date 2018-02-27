<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   FrameworkCache
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/15
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

class FrameworkCache
{
    const DBTYPE_SQL = 1;
    const DBTYPE_REDIS = 2;

    private $m_oCore;
    private $m_dbtype = 0;
    private $m_sqlSession = NULL;
    private $m_redisSession = NULL;

    public function __construct($oCore)
    {
        $this->m_oCore = $oCore;
        $oConfig = $oCore->getConfig();
        $this->m_redisSession = $oCore->_getFrameworkRedisSession();
        $this->m_sqlSession = $oCore->_getFrameworkSqlSession();
        if($this->m_redisSession)
        {
            // use redis
            $this->m_dbtype = self::DBTYPE_REDIS;
        }else{
            // use mysql
            $this->m_dbtype = self::DBTYPE_SQL;
        }
    }

    public function getRaw($key)
    {
        switch($this->m_dbtype)
        {
            case self::DBTYPE_SQL:
                $dbres = $this->m_sqlSession->queryRaw("SELECT `data` FROM `".$this->m_oCore->_getFrameworkSqlTable('cache')."` WHERE `key`=?", array($key));
                $dbrow = $dbres->fetch_array(\JsGreenTeaPHPFramework\core\SqlSession::FLAG_NUM);
                $dbres->close();
                return $dbrow[0];
            case self::DBTYPE_REDIS:
                return substr($this->m_redisSession->get($this->m_oCore->_getFrameworkRedisKey($key)), 1);
        }
        return false;
    }

    public function setRaw($key, $value)
    {
        switch($this->m_dbtype)
        {
            case self::DBTYPE_SQL:
                $dbres = $this->m_sqlSession->queryRaw("INSERT INTO `".$this->m_oCore->_getFrameworkSqlTable('cache')."` (`key`,`data`,`assist`) VALUES (?, ?, 0) ON DUPLICATE KEY UPDATE `data`=?",
                    array($key, $value, $value));
                if($dbres)
                {
                    $dbres->close();
                    return true;
                }else{
                    return false;
                }
            case self::DBTYPE_REDIS:
                return $this->m_redisSession->set($this->m_oCore->_getFrameworkRedisKey($key), ';'.$value);
        }
        return false;
    }

    public function getEx($key)
    {
        switch($this->m_dbtype)
        {
            case self::DBTYPE_SQL:
                $dbres = $this->m_sqlSession->queryRaw("SELECT `data`,`assist`, `flags` FROM `".$this->m_oCore->_getFrameworkSqlTable('cache')."` WHERE `key`=?", array($key), true);
                $dbrow = $dbres->fetch_array(\JsGreenTeaPHPFramework\core\SqlSession::FLAG_NUM);
                $dbres->close();
                return $dbrow;
            case self::DBTYPE_REDIS:
                $serializedData = $this->m_redisSession->get($this->m_oCore->_getFrameworkRedisKey($key));
                if(!$serializedData)
                    return NULL;
                if(gettype($serializedData) == "string")
                    return json_decode($serializedData);
                else
                    return $serializedData;
        }
        return false;
    }

    public function setEx($key, &$value, $assist = 0, $flags = 0)
    {
        switch($this->m_dbtype)
        {
            case self::DBTYPE_SQL:
                $dbres = $this->m_sqlSession->queryRaw("INSERT INTO `".$this->m_oCore->_getFrameworkSqlTable('cache')."` (`key`,`data`,`assist`, `flags`) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `data`=?, `assist`=?, `flags`=?",
                    array($key, $value, $assist, $flags, $value, $assist, $flags));
                if($dbres)
                {
                    $dbres->close();
                    return true;
                }else{
                    return false;
                }
            case self::DBTYPE_REDIS:
                $serializedData = json_encode(array($value, $assist, $flags), JSON_UNESCAPED_UNICODE);
                return $this->m_redisSession->set($this->m_oCore->_getFrameworkRedisKey($key), $serializedData);
        }
        return false;
    }

    public function setManyEx($pairs)
    {
        switch($this->m_dbtype)
        {
            case self::DBTYPE_SQL:
                $autocommit = $this->m_sqlSession->getAutocommit();
                $this->m_sqlSession->setAutocommit(false);
                $this->m_sqlSession->begin_transaction();
                $stmt = $this->m_sqlSession->prepare("INSERT INTO `".$this->m_oCore->_getFrameworkSqlTable('cache')."` (`key`,`data`,`assist`,`flags`) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `data`=?, `assist`=?, `flags`=?");
                foreach($pairs as $key => $value)
                {
                    $stmt->bind_param('ssiisii', $key, $value[0], $value[1], $value[2], $value[0], $value[1], $value[2]);
                    $stmt->execute();
                }
                $stmt->close();
                $this->m_sqlSession->commit();
                $this->m_sqlSession->setAutocommit($autocommit);
                return true;
            case self::DBTYPE_REDIS:
                $redisdict = array();
                foreach($pairs as $key => $value)
                {
                    $redisdict[$this->m_oCore->_getFrameworkRedisKey($key)] = json_encode(array($value[0], $value[1], $value[2]), JSON_UNESCAPED_UNICODE);
                }
                return $this->m_redisSession->mset($redisdict);
        }
        return false;
    }

    public function get($key)
    {
        $data = $this->getEx($key);
        if(is_array($data))
        {
            return $data[0];
        }else{
            return $data;
        }
    }

    public function set($key, &$value)
    {
        return $this->setEx($key, $value);
    }

    public function del($keySearchPhrase)
    {
        switch($this->m_dbtype)
        {
            case self::DBTYPE_SQL:
                $keySearchPhrase = str_replace("*", "%", $keySearchPhrase);
                $autocommit = $this->m_sqlSession->getAutocommit();
                $this->m_sqlSession->setAutocommit(true);
                $dbres = $this->m_sqlSession->queryRaw("DELETE FROM `".$this->m_oCore->_getFrameworkSqlTable('cache')."` WHERE `key` LIKE ?", array($keySearchPhrase));
                $dbres->close();
                $this->m_sqlSession->setAutocommit($autocommit);
                return true;
            case self::DBTYPE_REDIS:
                $it = 0;
                $deleteKeys = array();
                $keySearchPhrase = $this->m_oCore->_getFrameworkRedisKey($keySearchPhrase);
                do {
                    $scanres = $this->m_redisSession->scan($it, array('MATCH' => addslashes($keySearchPhrase), 'COUNT' => 100));
                    if($scanres)
                    {
                        $it = $scanres[0];
                        foreach($scanres[1] as $strKey)
                        {
                            $deleteKeys[] = $strKey;
                        }
                    }
                } while ($scanres && $it > 0);
                return $this->m_redisSession->del($deleteKeys);
        }
        return false;
    }

    public function getViewCache($lang, $name, $modifiedtime)
    {
        $name = str_replace('/', '.', $name);
        $name = str_replace('\\', '.', $name);
        $key = 'viewcache:'.$lang.':'.$name;
        $data = $this->getEx($key);
        if($data[1] != $modifiedtime)
            return NULL;
        return $data[0];
    }

    public function setViewCache($lang, $name, $modifiedtime)
    {
        $name = str_replace('/', '.', $name);
        $name = str_replace('\\', '.', $name);
        $key = 'viewcache:'.$lang.':'.$name;
        return $this->setEx($key, $modifiedtime);
    }
};
