<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   SkinSupportInterceptor
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2017/12/20
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\skin;

class SkinSupportInterceptor extends \JsGreenTeaPHPFramework\core\HandlerInterceptor
{
    private $m_skinViewPath = "skin/";
    private $m_skinResPath = "skin/";
    private $m_skinName = "";

    public function setSkinViewPath($skinPath)
    {
        $this->m_skinViewPath = $skinPath;
    }

    public function setSkinResPath($resPath)
    {
        $this->m_skinResPath = $resPath;
    }

    public function setDefaultSkin($skinName)
    {
        $this->m_skinName = $skinName;
    }

    public function preHandle(&$request, &$response, $rev)
    {
        $messageSource = self::getAutoWiredObject("messageSource");
        if($messageSource)
        {
            $messageSource->addMessagesDir($this->m_skinResPath.$this->m_skinName);
        }
        return true;
    }

    public function postHandle(&$request, &$response, $rev, &$oModelAndView)
    {
        $viewName = $oModelAndView->getViewName();
        if($viewName) {
            if($this->m_skinName) {
                $newviewname = $this->m_skinViewPath . $this->m_skinName . '/' . $viewName;
                $viewfilepath = self::getCore()->getWorkDir() . '/view/' . $newviewname . '.php';
                if(file_exists($viewfilepath))
                {
                    $oModelAndView->setViewName($newviewname);
                }
            }
        }
        return true;
    }
}