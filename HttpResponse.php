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

namespace JsGreenTeaPHPFramework;

require_once(__DIR__.'/HttpStatus.php');

class HttpResponse
{
    private $m_oCore;
    private $m_httpcode = HttpStatus::HTTP_OK;

    public function __construct($oCore)
    {
        $this->m_oCore = $oCore;
    }

    public function setStatus($httpcode)
    {
        $this->m_httpcode = $httpcode;
    }

    public function getStatus()
    {
        return $this->m_httpcode;
    }
}
