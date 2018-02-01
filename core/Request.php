<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   Request
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/14
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */


namespace JsGreenTeaPHPFramework\core;

require_once(__DIR__.'/ModelAndView.php');

class Request
{
    private $m_oCore;
    private $m_oModelAndView;
    private $m_parameters;
    private $m_session;
    private $m_pageSession = array();
    private $m_urlPath;

    public function __construct($oCore)
    {
        $this->m_oCore = $oCore;
        $this->m_parameters = $_GET;
        $this->m_session = $oCore->createSessionObject();
        $this->m_session->init();
    }

    public function _setUrlPath($uri_path)
    {
        $this->m_urlPath = $uri_path;
    }

    public function getUrlPath()
    {
        return $this->m_urlPath;
    }

    public function _setModelAndView(&$object)
    {
        $t_object = mb_strtolower(gettype($object));
        if(strcmp($t_object, "string") == 0)
        {
            $this->m_oModelAndView = new ModelAndView();
            $this->m_oModelAndView->setViewName($object);
        }else if(strcmp($t_object, "null") == 0){
            $this->m_oModelAndView = new ModelAndView();
        }else if(strcmp($t_object, "object") == 0){
            $cn_object = get_class($object);
            if(strcmp($cn_object, "JsGreenTeaPHPFramework\\core\\ModelAndView") == 0)
            {
                $this->m_oModelAndView = $object;
            }
        }else{
            die("Wrong type");
        }
    }

    public function _setParameters(&$params)
    {
        $this->m_parameters = array_merge($_GET, $params);
    }

    public function &_getModelAndView()
    {
        if(!$this->m_oModelAndView)
        {
            $this->m_oModelAndView = new ModelAndView();
        }
        return $this->m_oModelAndView;
    }

    public function &getAttribute($key)
    {
        return $this->m_oModelAndView->getAttribute($key);
    }

    public function &getAttributes()
    {
        return $this->m_oModelAndView->getAttributes();
    }

    public function getParameter($key)
    {
        return $this->m_parameters[$key];
    }

    public function &getParameters()
    {
        return $this->m_parameters;
    }

    public function &getSession()
    {
        return $this->m_session;
    }

    public function &getPageSession()
    {
        return $this->m_pageSession;
    }
};
