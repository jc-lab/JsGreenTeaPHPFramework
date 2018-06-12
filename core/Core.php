<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   Core
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/14
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

use JsGreenTeaPHPFramework\core\FrameworkCache;
use JsGreenTeaPHPFramework\i18n\AcceptHeaderLocaleResolver;

class Core
{
    private $m_website_name;
    private $m_oConfig;
    private $m_configs;
    private $m_workdir;

    private $m_current_siteroot;
    private $m_current_rootpath;

    private $m_oFrameworkSqlSession = NULL;
    private $m_oFrameworkRedisSession = NULL;
    private $m_oSiteSqlSession = NULL;

    private $m_oResourceManager = NULL;
    private $m_oAutoWiring = NULL;

    private $m_oDefaultHttpInterceptor;

    private $m_oUrlInterceptorsFW = array();
    private $m_oUrlInterceptorsUSER = array();
    private $m_oUrlInterceptors = array();

    private $m_oDefaultLocaleResolver = NULL;

    private $m_oSubObjects = array();

    public function &_getFrameworkInternalObject($name)
    {
        return $this->m_oSubObjects[$name];
    }

    public function &getAutoWiredObject($id)
    {
        return $this->m_oAutoWiring->getObject($id);
    }

    public function __construct($websitename, $rooturi, $workdir = NULL)
    {
        $rooturi = str_replace('\\', '/', $rooturi);
        if($workdir)
            $this->m_workdir = $workdir;
        else
            $this->m_workdir = 'greentea.'.$websitename;
        require_once($this->m_workdir.'/config.php');
        $this->m_website_name = $websitename;
        $this->m_current_siteroot = $rooturi;
        if(substr($rooturi, strlen($rooturi) - 1, 1) != "/")
            $rooturi .= '/';
        $this->m_current_rootpath = $rooturi;
        $this->m_oConfig = new \JsGreenTeaPHPFramework\SiteConfig($websitename);
        $this->m_configs = &$this->m_oConfig->_friend_core();
        $this->m_oConfig->init();

        if($this->m_oConfig->frameworksql_host)
        {
            $this->m_oFrameworkSqlSession = new FrameworkSqlSession($this);
        }

        if($this->m_oConfig->sitesql_host) {
            if (($this->m_oConfig->frameworksql_host == $this->m_oConfig->sitesql_host) &&
                ($this->m_oConfig->frameworksql_port == $this->m_oConfig->sitesql_port) &&
                ($this->m_oConfig->frameworksql_username == $this->m_oConfig->sitesql_username) &&
                ($this->m_oConfig->frameworksql_dbname == $this->m_oConfig->sitesql_dbname)) {
                $this->m_oSiteSqlSession = new SiteSqlSession($this->m_oFrameworkSqlSession);
            } else {
                $this->m_oSiteSqlSession = new SiteSqlSession($this);
            }
        }

        if($this->m_oConfig->frameworkredis_host) {
            $this->m_oFrameworkRedisSession = new FrameworkRedisSession($this);
        }

        $this->m_oFrameworkCache = new FrameworkCache($this);
        $this->m_oResourceManager = new ResourceManager($this);
        $this->m_oAutoWiring = new AutoWiring($this, $this->m_oResourceManager);

        $this->m_oResourceManager->_init();
        $this->m_oAutoWiring->applyAutowiringToClass(get_class($this), $this);

        {
            $oCommon = $this->m_oResourceManager->_getCommon();
            if(isset($oCommon->{'authentication-manager'}))
            {
                $elementAuthenticationManager = $oCommon->{'authentication-manager'}[0];
                $attrs = $elementAuthenticationManager->attributes();
                if(isset($attrs['ref']))
                {
                    $this->m_oSubObjects['authenticationManager'] = $this->m_oAutoWiring->getObject($attrs['ref']->__toString());
                }else{
                    // Create default authenticationManager
                }
                //children
                foreach($elementAuthenticationManager->children() as $item)
                {
                    $tagName = strtolower($item->getName());
                    if($tagName == 'authentication-provider')
                    {
                        $subattrs = $item->attributes();
                        if(isset($subattrs['user-service-ref']))
                        {
                            $this->m_oSubObjects['authenticationManager']->setAuthenticationProvider($this->m_oAutoWiring->getObject($subattrs['user-service-ref']->__toString()));
                        }
                    }
                }
            }
        }

        $this->m_oDefaultHttpInterceptor = new DefaultHttpInterceptor($this->m_oResourceManager);
        $this->_initFrameworkObject($this->m_oDefaultHttpInterceptor);
        $this->addFrameworkUrlInterceptor($this->m_oDefaultHttpInterceptor);

        $this->m_oAutoWiring->setObject('sqlSession', $this->m_oSiteSqlSession);

        if($this->m_oResourceManager->getResSettingBool("security:csrf:enabled"))
        {
            $this->m_oSubObjects['security_csrfManager'] = new \JsGreenTeaPHPFramework\security\csrf\CsrfManager();
            $this->m_oSubObjects['security_csrfInterceptor'] = new \JsGreenTeaPHPFramework\security\csrf\CsrfInterceptor();
            $this->_initFrameworkObject($this->m_oSubObjects['security_csrfManager']);
            $this->_initFrameworkObject($this->m_oSubObjects['security_csrfInterceptor']);
            $this->m_oSubObjects['security_csrfInterceptor']->setCsrfManager($this->m_oSubObjects['security_csrfManager']);
            $this->m_oAutoWiring->applyAutowiringToClass(get_class($this->m_oSubObjects['security_csrfManager']), $this->m_oSubObjects['security_csrfManager']);
            $this->addFrameworkUrlInterceptor($this->m_oSubObjects['security_csrfInterceptor']);
        }

        $this->m_oAutoWiring->setObject('resourceManager', $this->m_oResourceManager);
        if(!$this->m_oAutoWiring->existsObject('localeResolver'))
        {
            $this->m_oDefaultLocaleResolver = new AcceptHeaderLocaleResolver();
            $this->m_oAutoWiring->setObject('localeResolver', $this->m_oDefaultLocaleResolver);
        }

        $this->m_oSubObjects['siteCache'] = new SiteCache($this->m_oFrameworkCache);
        $this->_initFrameworkObject($this->m_oSubObjects['siteCache']);
        $this->m_oAutoWiring->setObject('siteCache', $this->m_oSubObjects['siteCache']);
    }

    // Function to get the client ip address
    public function getClientIP() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'])
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'])
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']) && $_SERVER['HTTP_X_FORWARDED'])
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']) && $_SERVER['HTTP_FORWARDED_FOR'])
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']) && $_SERVER['HTTP_FORWARDED'])
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'])
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = '*UNKNOWN';

        return $ipaddress;
    }

    public function getServerRes($name)
    {
        $name = strtoupper($name);
        if(strcmp($name, "SITEWORKDIR") == 0)
        {
            return $this->m_workdir;
        }else if(strcmp($name, "SITEROOT_URI") == 0)
        {
            return $this->m_current_siteroot;
        }else if(strcmp($name, "REQUEST_URI") == 0)
        {
            return $_SERVER['REQUEST_URI'];
        }else if(strcmp($name, "HTTP_HOST") == 0)
        {
            return $_SERVER['HTTP_HOST'];
        }else if(strcmp($name, "SITEROOT_FULLURI") == 0)
        {
            $siteroot = $this->m_current_siteroot;
            $len = strlen($siteroot) - 1;
            if(substr($siteroot, $len, 1) == '/') {
                if($len <= 0)
                    $siteroot = "";
                else
                    $siteroot = substr($siteroot, $len);
            }
            return $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$siteroot;
        }else if(strcmp($name, 'SESSION_COOKIE_NAME') == 0)
        {
            return $this->m_oConfig->session_cookiename;
        }
        return NULL;
    }

    public function resolveResOnce($text, $locale = null, $oView = null)
    {
        $type = substr($text, 0, 1);
        $name = substr($text, 2, strlen($text)-3);
        if ($type == '$') {
            $result = $this->getFrameworkAttribute($name, $locale, $oView);
            return $result;
        } else if ($type == '#') {
            $arr = explode('/', $name, 2);
            if (count($arr) > 1) {
                if (strcmp($arr[0], "server") == 0) {
                    return $this->getServerRes($arr[1]);
                } else if (strcmp($arr[0], "string") != 0) {
                    return NULL;
                }
                $name = $arr[1];
            }/* else {
                $name = $text;
            }*/
            $result = $this->m_oResourceManager->getResString($name);
            if (!$result) {
                $oMessageSource = $this->m_oAutoWiring->getObject('messageSource');
                if ($oMessageSource) {
                    $result = $oMessageSource->getMessage($name, NULL, $locale);
                }
            }
            return $result;
        }
    }

    public function resolveRes($text, $locale = null, $oView = null, $attributes = null, $attributeCallbacks = null)
    {
        $oReplaceCB = new \JsGreenTeaPHPFramework\core\internal\ContentVariableReplaceCallback($this, $locale, $oView, $attributes, $attributeCallbacks);
        $tmparr = array($text, substr($text, 2, strlen($text) - 3));
        $content = $oReplaceCB->cbreplace($tmparr);
        return $content;
    }

    public function parseRes($text, $locale = null, $oView = null, $attributes = null, $attributeCallbacks = null)
    {
        return \JsGreenTeaPHPFramework\core\internal\ContentVariableReplaceCallback::replace($text, $this, $locale, $oView, $attributes, $attributeCallbacks);
    }

    public static function getTickCount()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public function &getConfig()
    {
        return $this->m_oConfig;
    }

    public function getSiteSqlTable($name)
    {
        return $this->m_oConfig->sitesql_tableprefix.$name;
    }

    public function _getFrameworkSqlTable($name)
    {
        return $this->m_oConfig->frameworksql_tableprefix.$name;
    }

    public function _getFrameworkRedisKey($name)
    {
        return $this->m_oConfig->frameworkredis_keyprefix.$name;
    }

    public function &getSiteSqlSession()
    {
        return $this->m_oSiteSqlSession;
    }

    public function &_getFrameworkSqlSession()
    {
        return $this->m_oFrameworkSqlSession;
    }

    public function &_getFrameworkRedisSession()
    {
        return $this->m_oFrameworkRedisSession;
    }

    public function &_getFrameworkCache()
    {
        return $this->m_oFrameworkCache;
    }

    public function &_getAutoWiring()
    {
        return $this->m_oAutoWiring;
    }

    public function createSessionObject()
    {
        if(!$this->m_oFrameworkSqlSession)
            return NULL;
        $session = new Session($this, $this->getConfig());
        $this->m_oAutoWiring->applyAutowiringToClass(get_class($session), $session);
        return $session;
    }

    public function setUrlInterceptors($interceptors)
    {
        $this->m_oUrlInterceptorsUSER = $interceptors;
        $this->m_oUrlInterceptors = array_merge($this->m_oUrlInterceptorsFW, $this->m_oUrlInterceptorsUSER);
    }

    private function addFrameworkUrlInterceptor($interceptor)
    {
        $this->m_oUrlInterceptorsFW[] = $interceptor;
        $this->m_oUrlInterceptors = array_merge($this->m_oUrlInterceptorsFW, $this->m_oUrlInterceptorsUSER);
    }

    public function _initFrameworkObject($object)
    {
        if(is_subclass_of($object, 'JsGreenTeaPHPFramework\\core\\FrameworkObject'))
        {
            $object->_setCore($this);
        }
    }

    public function &_getResourceManager()
    {
        return $this->m_oResourceManager;
    }

    public function getFrameworkAttribute($name, $locale = null, $oView = null)
    {
        return NULL;
    }

    public function getCommonResSetting($name)
    {
        $this->m_oResourceManager->getResSetting($name);
    }

    public function getCommonResSettingBool($name)
    {
        $this->m_oResourceManager->getResSettingBool($name);
    }

    public function getCommonResString($name)
    {
        return $this->m_oResourceManager->getResString($name);
    }

    private function show(&$request, &$response)
    {
        $requri = substr($_SERVER['REQUEST_URI'], strlen($this->m_current_rootpath));
        $uri = parse_url($requri);
        if(!isset($uri['path']))
            $uri_path = '/';
        else
            $uri_path = ((substr($uri['path'], 0, 1) != "/") ? "/" : "").$uri['path'];

        $request->_setUrlPath($uri_path);

        // Find Controller in routes
        $matchedpathkey = NULL;
        $endbyslash = substr($uri_path, strlen($uri_path) - 1, 1) == '/';
        foreach ($this->m_configs['routes'] as $key => $value) {
            $key_endbyslash = substr($key, -1, 1) == '/';

            // need cache
            if($endbyslash || $key_endbyslash)
            {
                if (strpos($uri_path, $key) === 0) {
                    if (strlen($key) > strlen($matchedpathkey))
                        $matchedpathkey = $key;
                }
            }else{
                if (strcmp($uri_path, $key) == 0)
                {
                    $matchedpathkey = $key;
                    break;
                }
            }
        }
        if(!$matchedpathkey)
        {
            $response->setStatus(HttpStatus::HTTP_NOT_FOUND);
            $request->_getModelAndView()->_execute($this, $request, $response);
        }else{
            $clsName = '\\'.$this->m_configs['routes'][$matchedpathkey];
            $tmpfilepath = str_replace('\\', '/', $this->m_configs['routes'][$matchedpathkey]);
            $controller_path = $this->m_workdir.'/'.$tmpfilepath.'.php';
            if(file_exists($controller_path))
            {
                require_once($controller_path);
                $basename = basename($controller_path, ".php");
                if(class_exists($clsName))
                {
                    $bInterceptorResult = true;

                    $lastchr = substr($matchedpathkey, -1, 1);
                    $n = strlen($matchedpathkey);
                    $pagepath = substr($uri_path, ($lastchr == '/') ? $n - 1 : $n);
                    if(!$pagepath)
                        $pagepath = "";
                    $controller = new $clsName($this);
                    $this->_initFrameworkObject($controller);
                    $controller->init();

                    foreach($this->m_oUrlInterceptors as &$interceptor)
                    {
                        if(!($bInterceptorResult = $interceptor->preHandle($request, $response, NULL)))
                        {
                            break;
                        }
                    }

                    if($bInterceptorResult) {
                        $object = $controller->_invoke($request, $response, $pagepath);
                        $request->_setModelAndView($object);

                        foreach($this->m_oUrlInterceptors as &$interceptor)
                        {
                            if(!($bInterceptorResult = $interceptor->postHandle($request, $response, NULL, $request->_getModelAndView())))
                            {
                                break;
                            }
                        }

                        $request->_getModelAndView()->_execute($this, $request, $response);
                    }

                    if(!$bInterceptorResult)
                    {
                        $response->setStatus(HttpStatus::HTTP_INTERNAL_SERVER_ERROR);
                        $request->_getModelAndView()->_execute($this, $request, $response);
                    }
                }else{
                    $response->setStatus(HttpStatus::HTTP_NOT_IMPLEMENTED);
                    $request->_getModelAndView()->_execute($this, $request, $response);
                }
            }else{
                $response->setStatus(HttpStatus::HTTP_NOT_IMPLEMENTED);
                $request->_getModelAndView()->_execute($this, $request, $response);
            }
        }
    }

    public function getWorkDir()
    {
        return $this->m_workdir;
    }

    public function index($rooturi)
    {
        $request = new Request($this);
        $response = new HttpResponse($this);
        $pageSession = &$request->getPageSession();
        $pageSession['session'] = $request->getSession();
        $authenticationManager = $this->_getFrameworkInternalObject('authenticationManager');
        if($authenticationManager)
            $authenticationManager->checkLogon($request);
        $this->show($request, $response);
    }
};

if(!function_exists("hex2bin"))
{
    function hex2bin($input)
    {
        return pack("H*", $input);
    }
}

