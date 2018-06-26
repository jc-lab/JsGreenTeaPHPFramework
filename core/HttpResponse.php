<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   HttpRequest
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/14
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

require_once(__DIR__.'/HttpStatus.php');

class HttpResponse
{
    private $m_oCore;
    private $m_oRequest;
    private $m_httpcode = HttpStatus::HTTP_OK;

    private $m_outputState = 0;

    public function __construct($oCore, $oRequest)
    {
        $this->m_oCore = $oCore;
        $this->m_oRequest = $oRequest;
    }

    public function setStatus($httpcode)
    {
        $this->m_httpcode = $httpcode;
    }

    public function getStatus()
    {
        return $this->m_httpcode;
    }

    public function beginOutput()
    {
        if($this->m_outputState == 0)
        {
            header($_SERVER['SERVER_PROTOCOL'].' '.$this->m_httpcode.' '.HttpStatus::getString($this->m_httpcode));
            $this->m_outputState = 1;

            if($this->m_oRequest)
            {
                $this->m_oRequest->onBeginResponse();
            }
        }
    }

    public function write($data)
    {
        echo $data;
    }
}
