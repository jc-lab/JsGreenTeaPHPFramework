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

class DefaultSession extends Session
{
    private $sessionRepository;
    private $attributeMap;
    private $sessionId;

    private $valid = true;

    public function __construct($sessionRepository, $sessionId, $attributeMap)
    {
        $this->sessionRepository = $sessionRepository;
        $this->sessionId = $sessionId;
        $this->attributeMap = $attributeMap;
        if(!$this->sessionId)
            $this->valid = false;
    }

    public function getId()
    {
        return $this->sessionId;
    }

    public function getSessionId()
    {
        return $this->sessionId;
    }

    public function setAttribute($key, $value)
    {
        $this->attributeMap[$key] = $value;
    }

    public function getAttribute($key)
    {
        return @$this->attributeMap[$key];
    }

    public function getAttributes()
    {
        return $this->attributeMap;
    }

    public function destroy()
    {
        $this->sessionRepository->deleteById($this->sessionId);
        $this->valid = false;
    }

    public function isValid() {
        return $this->valid;
    }
};
