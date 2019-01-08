<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   Controller
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/14
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core;

class Controller extends FrameworkObject
{
    private $m_routes;

    const METHOD_BOTH = 0;
    const METHOD_GET = 1;
    const METHOD_POST = 2;

    public function __construct($oCore)
    {
        $this->m_routes = array();
    }

    public function getSiteSqlTable($name)
    {
        return $this->m_oCore->getSiteSqlTable($name);
    }

    public function viewExample(&$request, &$response, &$parameters)
    {
        // $response : HttpResponse object
        // $parameters : URL route parameters
        // $response.setStatus(HttpStatus.HTTP_NOT_FOUND);
        // return "example"; // -> view/example.php
    }

    //RequestMapping
    public function setRoute($path, $callback, $method = self::METHOD_BOTH)
    {
        $this->m_routes[] = self::parseUrlExpression($path, $callback, $method);
    }

    public function _invoke(&$request, &$response, $pagepath)
    {
        $strhttpmethod = strtoupper($_SERVER['REQUEST_METHOD']);
        $nhttpmethod = 0;
        if(strcmp($strhttpmethod, "GET") == 0)
        {
            $nhttpmethod = self::METHOD_GET;
        }else if(strcmp($strhttpmethod, "POST") == 0)
        {
            $nhttpmethod = self::METHOD_POST;
        }

        $parameters = array();
        $routeinfo = NULL;
        /*if(array_key_exists($pagepath, $this->m_routes))
        {
            $routeinfo = $this->m_routes[$pagepath];
        }else{
            foreach($this->m_routes as $key => $value)
            {
                if($value['useregex']) {
                    $matchedarr = NULL;
                    $matchedcount = preg_match_all($value['regex'], $pagepath, $matchedarr);
                    if($matchedcount > 0)
                    {
                        for($i=0; $i<count($matchedarr[1]); $i++)
                        {
                            $parameters[$value['vars'][$i]] = $matchedarr[1][$i];
                        }
                        $request->_setParameters($parameters);
                    }
                }
            }
        }*/

        foreach($this->m_routes as &$item)
        {
            $item_path = $item['path'];

            if($item_path == $pagepath)
            {
                if(($item['method'] == self::METHOD_BOTH) || ($item['method'] == $nhttpmethod)) {
                    $routeinfo = $item;
                    break;
                }
            }else
            if($item['useregex']) {
                $matchedarr = NULL;
                $matchedcount = preg_match_all($item['regex'], $pagepath, $matchedarr);
                if($matchedcount > 0 && (($item['method'] == self::METHOD_BOTH) || ($item['method'] == $nhttpmethod)))
                {
                    $routeinfo = $item;
                    for($i=0; $i<count($matchedarr[1]); $i++)
                    {
                        $parameters[$item['vars'][$i]] = $matchedarr[1][$i];
                    }
                    $request->_setParameters($parameters);
                    break;
                }
            }
        }

        if(!$routeinfo)
        {
            $response->setStatus(HttpStatus::HTTP_NOT_FOUND);
            return NULL;
        }

        $callback = $routeinfo['callback'];
        return $this->$callback($request, $response, $parameters);
    }

    private static function parseUrlExpression($path, $callback, $method)
    {
        $newitem = array(
            'path' => $path,
            'callback' => $callback,
            'regex' => NULL,
            'useregex' => false,
            'method' => $method,
            'vars' => array()
        );

        $newregex = "/^";
        $vars = array();

        $tmpmatches = NULL;
        preg_match_all('/\{([^{}]+)\}/', $path, $tmpmatches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);
        $prevpos = 0;
        for($i=0;$i<count($tmpmatches[1]);$i++)
        {
            $vars[] = $tmpmatches[1][$i][0];
            $temp = substr($path, $prevpos, $tmpmatches[1][$i][1] - 1 - $prevpos);
            $temp = preg_quote($temp, '/');
            $newregex .= $temp;
            $newregex .= '([^\/]+)';
            $prevpos = $tmpmatches[1][$i][1] + strlen($tmpmatches[1][$i][0]) + 1;
        }
        $temp = substr($path, $prevpos);
        $temp = preg_quote($temp, '/');
        $newregex .= $temp . '$/';

        if(count($tmpmatches[1]) > 0)
        {
            $newitem['useregex'] = true;
        }
        $newitem['regex'] = $newregex;
        $newitem['vars'] = $vars;

        return $newitem;
    }

    // default Exception handler
    public function exceptionHandler($exception, $request, $response) {
        throw $exception;
    }
}
