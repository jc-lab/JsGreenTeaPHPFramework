<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   SpringSessionRepository
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2019/01/05
 * @copyright Copyright (C) 2018 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

class SpringSessionRepository extends FrameworkObject implements SessionRepository
{
    private $redisSessionDataPrefix = "spring:session:sessions:";
    private $sessionAttrKeyPrefix = "sessionAttr:";

    public function refresh($sessionId) {
        $redisSession = $this->m_oCore->_getFrameworkRedisSession();
        $redisSession->hset($this->redisSessionDataPrefix.$sessionId, 'lastAccessedTime', time() * 1000);
    }

    public function createSession() {
        $redisSession = $this->m_oCore->_getFrameworkRedisSession();
        $sessionId = self::generateUUID();

        $redisSession->hset($this->redisSessionDataPrefix.$sessionId, 'creationTime', time() * 1000);
        $redisSession->hset($this->redisSessionDataPrefix.$sessionId, 'maxInactiveInterval', 1800);

        $this->refresh($sessionId);

        return NULL;
    }

    public function save($session) {
        $redisSession = $this->m_oCore->_getFrameworkRedisSession();
        $sessionId = $session->getId();
        $attributeMap = $session->getAttributes();
        foreach($attributeMap as $key => $value) {
            $jsonValue = json_encode($value, JSON_UNESCAPED_UNICODE);
            $redisSession->hset($this->redisSessionDataPrefix.$sessionId, $this->sessionAttrKeyPrefix.$key, $jsonValue);
        }
    }

    public function findById($sessionId) {
        $attributeMap = array();
        $redisSession = $this->m_oCore->_getFrameworkRedisSession();
        $sessionDataMap = $redisSession->hgetall($this->redisSessionDataPrefix.$sessionId);
        $redisSession->hset($this->redisSessionDataPrefix.$sessionId, 'lastAccessedTime', time() * 1000);
        foreach($sessionDataMap as $key => $value) {
            $temp = substr($key, 0, strlen($this->sessionAttrKeyPrefix));
            if($temp == $this->sessionAttrKeyPrefix) {
                $attrKey = substr($key, strlen($this->sessionAttrKeyPrefix));
                $valObject = json_decode($value, true);
                $attributeMap[$attrKey] = $valObject;
            }
        }
        $session = new DefaultSession($this, $sessionId, $attributeMap);

        $this->refresh($sessionId);

        return $session;
    }

    public function deleteById($sessionId) {
        $redisSession = $this->m_oCore->_getFrameworkRedisSession();
        $redisSession->del($this->redisSessionDataPrefix.$sessionId);
    }

    private static function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

