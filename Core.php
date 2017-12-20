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

namespace JsGreenTeaPHPFramework;

use JsGreenTeaPHPFramework\i18n\AcceptHeaderLocaleResolver;

require_once(__DIR__."/BaseConfig.php");
require_once(__DIR__.'/FrameworkObject.php');
require_once(__DIR__.'/SqlSession.php');
require_once(__DIR__.'/FrameworkSqlSession.php');
require_once(__DIR__.'/SiteSqlSession.php');
require_once(__DIR__.'/RedisSession.php');
require_once(__DIR__.'/FrameworkRedisSession.php');
require_once(__DIR__.'/FrameworkCache.php');
require_once(__DIR__.'/Session.php');
require_once(__DIR__."/HttpStatus.php");
require_once(__DIR__."/HttpResponse.php");
require_once(__DIR__."/Request.php");
require_once(__DIR__."/Controller.php");
require_once(__DIR__.'/ModelAndView.php');
require_once(__DIR__.'/ResourceManager.php');
require_once(__DIR__.'/AutoWiring.php');

require_once(__DIR__.'/HandlerInterceptor.php');
require_once(__DIR__.'/Locale.php');
require_once(__DIR__.'/LocaleResolver.php');
require_once(__DIR__ . '/i18n/AcceptHeaderLocaleResolver.php');

class Core
{
    private $m_website_name;
    private $m_oConfig;
    private $m_configs;
    private $m_current_rootpath;
    private $m_workdir;

    private $m_oFrameworkSqlSession = NULL;
    private $m_oFrameworkRedisSession = NULL;
    private $m_oSiteSqlSession = NULL;

    private $m_oResourceManager = NULL;
    private $m_oAutoWiring = NULL;
    private $m_oUrlInterceptors = NULL;

    private $m_oDefaultLocaleResolver = NULL;

    private function __construct($websitename, $rooturi)
    {
        $this->m_workdir = 'greentea.'.$websitename;
        require_once($this->m_workdir.'/config.php');
        $this->m_website_name = $websitename;
        if(substr($rooturi, strlen($rooturi) - 1, 1) != "/")
            $rooturi .= '/';
        $this->m_current_rootpath = $rooturi;
        $this->m_oConfig = new SiteConfig($websitename);
        $this->m_configs = &$this->m_oConfig->_friend_core();
        $this->m_oConfig->init();
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

    public function setUrlInterceptors($interceptors)
    {
        $this->m_oUrlInterceptors = $interceptors;
    }

    public function _initFrameworkObject($object)
    {
        if(is_subclass_of($object, 'JsGreenTeaPHPFramework\\FrameworkObject'))
        {
            $object->_setCore($this);
        }
    }

    private function show()
    {
        $this->m_oFrameworkSqlSession = new FrameworkSqlSession($this);

        if(($this->m_oConfig->frameworksql_host == $this->m_oConfig->sitesql_host) &&
            ($this->m_oConfig->frameworksql_port == $this->m_oConfig->sitesql_port) &&
            ($this->m_oConfig->frameworksql_username == $this->m_oConfig->sitesql_username) &&
            ($this->m_oConfig->frameworksql_dbname == $this->m_oConfig->sitesql_dbname))
        {
            $this->m_oSiteSqlSession = new SiteSqlSession($this->m_oFrameworkSqlSession);
        }else{
            $this->m_oSiteSqlSession = new SiteSqlSession($this);
        }

        if($this->m_oConfig->frameworkredis_host) {
            $this->m_oFrameworkRedisSession = new FrameworkRedisSession($this);
        }

        $this->m_oFrameworkCache = new FrameworkCache($this);
        $this->m_oResourceManager = new ResourceManager($this);
        $this->m_oAutoWiring = new AutoWiring($this, $this->m_oResourceManager);

        $this->m_oResourceManager->_init();
        $this->m_oAutoWiring->applyAutowiringToClass(get_class($this), $this);

        $request = new Request($this);
        $response = new HttpResponse($this);

        $this->m_oAutoWiring->setObject('sqlSession', $this->m_oSiteSqlSession);
        $this->m_oAutoWiring->setObject('resourceManager', $this->m_oResourceManager);
        if(!$this->m_oAutoWiring->existsObject('localeResolver'))
        {
            $this->m_oDefaultLocaleResolver = new AcceptHeaderLocaleResolver();
            $this->m_oAutoWiring->setObject('localeResolver', $this->m_oDefaultLocaleResolver);
        }

        $requri = substr($_SERVER['REQUEST_URI'], strlen($this->m_current_rootpath));
        $uri = parse_url($requri);
        if(!isset($uri['path']))
            $uri_path = '/';
        else
            $uri_path = ((substr($uri['path'], 0, 1) != "/") ? "/" : "").$uri['path'];

        // Find Controller in routes
        $matchedpathkey = NULL;
        $endbyslash = substr($uri_path, strlen($uri_path) - 1, 1);
        foreach ($this->m_configs['routes'] as $key => $value) {
            // need cache
            if($endbyslash)
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
            $controller_path = 'greentea.'.$this->m_website_name.'/controllers/'.$this->m_configs['routes'][$matchedpathkey].'.php';
            if(file_exists($controller_path))
            {
                require_once($controller_path);
                $basename = basename($controller_path, ".php");
                if(class_exists($basename))
                {
                    $bInterceptorResult = true;

                    $pagepath = substr($uri_path, strlen($matchedpathkey));
                    if(!$pagepath)
                        $pagepath = "";
                    $controller = new $basename($this);
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

    public static function index($websitename, $rooturi)
    {
        $core = new Core($websitename, $rooturi);
        $core->show();
    }
};

if(!function_exists("hex2bin"))
{
    function hex2bin($input)
    {
        return pack("H*", $input);
    }
}
