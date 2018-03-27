<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   GTTLProcessBlock
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2018/03/24
 * @copyright Copyright (C) 2018 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\core\internal;

class GTTLProcessBlock
{
    public $blockvars = array();
    public $vars = array();

    public function __construct($parentBlock)
    {
        if($parentBlock)
        {
            $this->vars = $parentBlock->vars;
        }
    }

    private function internalProcess($curnode) {

    }

    private function block_setVar($name, $value)
    {
        if(isset($this->vars[$name]))
        {
            $this->vars[$name] = $value;
        }else{
            $this->blockvars[$name] = $value;
            $this->vars[$name] = &$this->blockvars[$name];
        }
    }

    /*
     * $newBlock = new
     * $newBlock->loopChilds(childrens);
     * */
    private function loopChilds($childrens)
    {
        $result = 1;
        foreach($childrens as &$node)
        {
            $result = $this->process($node);
            if($result != 1)
            {
                break;
            }
        }
        return $result;
    }

    public function process($curnode)
    {
        $tagname = substr(strtolower($curnode->tag), 2);
        if($tagname == 'set') {
            $attr_var = $curnode->getAttribute('var');
            $attr_value = $curnode->getAttribute('value');
            block_setVar($attr_var, $attr_value);
            return 1;
        }else if($tagname == 'if')
        {
            $strtest = $curnode->getAttribute('test');
            $condition = true;
            if(!$condition) {
                return 1;
            }
            return $this->loopChilds($curnode->children());
        }

        return 1;
    }
};
