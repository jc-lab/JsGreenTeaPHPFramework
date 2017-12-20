<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   AutoWiring
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/16
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework;

class AutoWiring
{
    private $m_oCore;
    private $m_oResourceManager;

    private $m_searchPaths;

    private $m_WiredObjects;

    const m_NULLObject = NULL;

    public function __construct($oCore, $oResourceManager)
    {
        $this->m_oCore = $oCore;
        $this->m_oResourceManager = $oResourceManager;
        $this->m_searchPaths = array($oCore->getWorkDir());
    }

    public function addSearchPath($path)
    {
        $this->m_searchPaths[] = $path;
    }

    private function _propertyParseSubElement(&$item, &$value)
    {
        $name = strtolower($item->getName());
        if(strcmp($name, "list") == 0)
        {
            $value = array();
            foreach($item->children() as $subitem)
            {
                $tempvalue = NULL;
                $this->_propertyParseSubElement($subitem, $tempvalue);
                $value[] = $tempvalue;
            }
            return 3;
        }else if(strcmp($name, "value") == 0)
        {
            $attrs = $item->attributes();
            if(isset($attrs['value']))
                $value = $attrs['value']->__toString();
            else
                $value = $item->__toString();
            return 1;
        }else if(strcmp($name, "ref") == 0)
        {
            $attrs = $item->attributes();
            if(isset($attrs['id']))
                $ref = $attrs['id']->__toString();
            else if(isset($attrs['ref']))
                $ref = $attrs['ref']->__toString();
            else
                $ref = $item->__toString();
            $value = $this->getObject($ref);
            return 2;
        }else{
            $value = $item->__toString();
            return 1;
        }
    }

    private function _propertyParse(&$item, &$attrs, &$value)
    {
        if(isset($attrs['value']))
        {
            $value = $this->_resolveString($attrs['value']->__toString());
            return 1;
        }else if(isset($attrs['ref']))
        {
            if(isset($attrs['ref']))
                $ref = $attrs['ref']->__toString();
            else
                $ref = $item->__toString();
            $value = $this->getObject($ref);
            return 2;
        }else{
            $children = $item->children();
            if(count($children) > 0)
            {
                return $this->_propertyParseSubElement($children[0], $value);
            }else{
                $value = $this->_resolveString($item->__toString());
            }
            return 3;
        }
    }

    private function _resolveString($string)
    {
        $oReplaceCB = new \JsGreenTeaPHPFramework\AutoWiring\_ContentVariableReplaceCallback();
        $oReplaceCB->oResourceManager = &$this->m_oResourceManager;
        $string = preg_replace_callback('/[\$#]{([^}]+)}/', array($oReplaceCB, "cbreplace"), $string);
        return $string;
    }

    private function _classIniter(&$xmlItems, &$object)
    {
        if(isset($xmlItems->{'property'}))
        {
            foreach($xmlItems->{'property'} as $subitem)
            {
                $subitem_attris = $subitem->attributes();
                $property_name = $subitem_attris['name'];
                $value = NULL;
                $this->_propertyParse($subitem, $subitem_attris, $value);
                $property_setterName = "set".strtoupper(substr($property_name, 0, 1)).substr($property_name, 1);
                $object->$property_setterName($value);
            }
        }
    }

    public function applyAutowiringToClass($className, &$object)
    {
        // Fixed objects
        $xmlCommon = $this->m_oResourceManager->_getCommon();
        if(isset($xmlCommon->AutoWiring))
        {
            foreach($xmlCommon->AutoWiring as $item)
            {
                $attributes = $item->attributes();
                if(strcasecmp($attributes['class'], $className) == 0)
                {
                    $children = $item->children();
                    $this->_classIniter($children, $object);
                }
            }
        }
    }

    public function existsObject($id)
    {
        $xmlCommon = $this->m_oResourceManager->_getCommon();
        if(isset($xmlCommon->AutoWiring)) {
            foreach ($xmlCommon->AutoWiring as $item) {
                $attributes = $item->attributes();
                if(strcasecmp($attributes['id'], $id) == 0)
                {
                    return true;
                }
            }
        }
        return false;
    }

    public function isLoadedObject($id)
    {
        return isset($this->m_WiredObjects[$id]);
    }

    public function setObject($id, &$object)
    {
        $id = strtolower($id);
        $this->m_WiredObjects[$id] = &$object;
    }

    public function &getObject($id)
    {
        // Fixed objects
        $xmlCommon = $this->m_oResourceManager->_getCommon();

        $id = strtolower($id);
        if(strcmp($id, "core") == 0)
        {
            return $this->m_oCore;
        }else if(isset($this->m_WiredObjects[$id]))
        {
            return $this->m_WiredObjects[$id];
        }else{
            if(isset($xmlCommon->AutoWiring))
            {
                foreach($xmlCommon->AutoWiring as $item)
                {
                    $attributes = $item->attributes();
                    $objectId = $attributes['id'];
                    if(strcmp(strtolower($objectId), $id) == 0)
                    {
                        $children = $item->children();
                        $className = $attributes['class'];
                        $classNameArr = explode('\\', $className);
                        $classPath = "";

                        $classFullPath = NULL;
                        $x = count($classNameArr);
                        $fwns = 'JsGreenTeaPHPFramework\\';
                        if(strncmp($className, $fwns, strlen($fwns)) == 0)
                        {
                            for ($i = 1; $i < $x; $i++) {
                                if (($i + 1) == $x) {
                                    $classPath .= $classNameArr[$i] . ".php";
                                } else {
                                    $classPath .= $classNameArr[$i] . "/";
                                }
                            }
                            $classFullPath = __DIR__.'/'.$classPath;
                            if(!file_exists($classFullPath))
                                $classFullPath = NULL;
                        }else {
                            for ($i = 0; $i < $x; $i++) {
                                if (($i + 1) == $x) {
                                    $classPath .= $classNameArr[$i] . ".php";
                                } else {
                                    $classPath .= $classNameArr[$i] . "/";
                                }
                            }
                        }

                        if($classFullPath) {
                            require_once($classFullPath);
                        }else{
                            foreach ($this->m_searchPaths as $searchPath) {
                                $classFullPath = $searchPath . '/' . $classPath;
                                if (file_exists($classFullPath)) {
                                    require_once($classFullPath);
                                    break;
                                }
                            }
                        }

                        $constructorArgs = array();
                        if(isset($children->{'constructor-arg'}))
                        {
                            foreach($children->{'constructor-arg'} as $subitem)
                            {
                                $subitem_attris = $subitem->attributes();
                                $index = intval($subitem_attris['index']);
                                $value = NULL;
                                $this->_propertyParse($subitem, $subitem_attris, $value);
                                $constructorArgs[$index] = $value;
                            }
                        }

                        // php >= 5.6
                        $className = '\\'.$className;
                        $this->m_WiredObjects[$id] = new $className(...$constructorArgs);
                        $object = &$this->m_WiredObjects[$id];
                        $this->m_oCore->_initFrameworkObject($object);

                        $this->_classIniter($children, $object);

                        return $this->m_WiredObjects[$id];
                    }
                }
            }
        }

        return $this->m_NULLObject;
    }
};

namespace JsGreenTeaPHPFramework\AutoWiring;

class _ContentVariableReplaceCallback
{
    public $oResourceManager;
    public function cbreplace($matches)
    {
        $type = substr($matches[0], 0, 1);
        if($type == '#')
        {
            $subtokens = explode('/', $matches[1], 2);
            if(count($subtokens) == 2)
            {
                if(strcmp(strtolower($subtokens[0]), "setting") == 0)
                {
                    return $this->oResourceManager->getResSetting($subtokens[1]);
                }else if(strcmp(strtolower($subtokens[0]), "string") == 0)
                {
                    return $this->oResourceManager->getResString($subtokens[1]);
                }
            }
        }
        return $matches[0];
    }
}
