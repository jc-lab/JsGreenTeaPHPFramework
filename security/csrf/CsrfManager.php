<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   CsrfManager
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/28
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\security\csrf;

class CsrfManager extends \JsGreenTeaPHPFramework\core\FrameworkObject
{
    private $m_paramName = "csrfToken";
    private $m_headerName = "Csrf-Header";
    private $m_sessionName = "csrftoken";

    public function setParamName($value)
    {
        $this->m_paramName = $value;
    }

    public function setHeaderName($value)
    {
        $this->m_headerName = $value;
    }

    public function setSessionName($value)
    {
        $this->m_sessionName = $value;
    }

    public function getParamName()
    {
        return $this->m_paramName;
    }

    public function getHeaderName()
    {
        return $this->m_headerName;
    }

    public function getNewToken(&$session)
    {
        $csrkToken = str_replace('=', '', base64_encode(openssl_random_pseudo_bytes(16)));
        $session->setAttribute($this->m_sessionName, $csrkToken);
        return $csrkToken;
    }

    public function validCsrf(&$request)
    {
        $pagesession = $request->getPageSession();
        if(isset($pagesession['csrfvalided']))
        {
            return $pagesession['csrfvalided'];
        }
        $session = $request->getSession();
        $valid = false;
        if(isset($_GET[$this->m_paramName]))
        {
            $paramToken = $_GET[$this->m_paramName];
            $valid = 2;
        }else if(isset($_POST[$this->m_paramName]))
        {
            $paramToken = $_POST[$this->m_paramName];
            $valid = 2;
        }
        if($valid == 2) {
            $sessToken = $session->getAttribute($this->m_sessionName);
            $session->setAttribute($this->m_sessionName, "");
            $valid = ($sessToken == $paramToken);
            $pagesession['csrfvalided'] = $valid;
        }
        if(!$valid)
        {
            throw new InvalidCsrfTokenException();
        }
        return $valid;
    }
}