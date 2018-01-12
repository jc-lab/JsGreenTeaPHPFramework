<?php
/**
 * JsGreenTeaPHPFramework - A PHP Framework
 *
 * @package JsGreenTeaPHPFramework
 * @class   OperatorComputeObject
 * @author  Jichan <development@jc-lab.net / http://ablog.jc-lab.net/category/JsGreenTeaPHPFramework>
 * @date    2018/01/01
 * @copyright Copyright (C) 2017 jichan(JC-Lab).\n
 *             This software may be modified and distributed under the terms
 *             of the MIT license.  See the LICENSE file for details.
 */

namespace JsGreenTeaPHPFramework\util;

class OperatorComputeObject
{
    const OPERAND_TYPE_USERDEFINED = 2;
    const OPERAND_TYPE_FUNCTION = 4;

    private $m_operatorStack;
    private $m_operandStack;
    private $m_operatorTop;
    private $m_operandTop;

    private $m_operandCallback;

    public function __construct()
    {
    }

    public function setOperandCallback($func, $param)
    {
        $this->m_operandCallback = array($func, $param);
        //callback($type, $name, $param, $callbackparam);
    }

    private function init()
    {
        $this->m_operatorStack = array();
        $this->m_operandStack = array();
        $this->m_operatorTop = 0;
        $this->m_operandTop = 0;
    }

    private function operatorPush($operator)
    {
        $this->m_operatorStack[$this->m_operatorTop++] = $operator;
    }

    private function operatorPop()
    {
        return $this->m_operatorStack[--$this->m_operatorTop];
    }

    private function operandPush($operator)
    {
        $this->m_operandStack[$this->m_operandTop++] = $operator;
    }

    private function operandPop()
    {
        if($this->m_operandTop <= 0)
            debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        return $this->m_operandStack[--$this->m_operandTop];
    }

    private function isEmptyOperatorStack()
    {
        return ($this->m_operatorTop == 0);
    }

    private function isEmptyOperandStack()
    {
        return ($this->m_operandTop == 0);
    }

    private function greaterOpr($opr1, $opr2)
    {
        if($opr1 == '*' || $opr1 == '/')
        {
            if(($opr2 == '+') || ($opr2 == '-'))
                return true;
            else
                return false;
        }else{
            return false;
        }
    }

    private function myEval($evaltext)
    {
        if(is_numeric($evaltext))
            return intval($evaltext);
        else if(is_bool($evaltext))
            return $evaltext;

        $evaltext = trim($evaltext);
        $matches = NULL;
        $findrst = preg_match('/(([A-Za-z0-9]+)\\(([^\\)]*)\\))/', $evaltext, $matches);
        if($findrst > 0) {
            $name = $matches[2];
            $argsMatches = NULL;
            $findrst = preg_match_all("/([^,]+\\(.+?\\))|([^,]+)/", $matches[3], $argsMatches);
            if($findrst > 0)
            {
                $args = array();
                foreach($argsMatches[0] as $item)
                {
                    $args[] = $this->myEval(trim($item));
                }
                return call_user_func($this->m_operandCallback[0], self::OPERAND_TYPE_FUNCTION, $name, $args, $this->m_operandCallback[1]);
            }else{
                return call_user_func($this->m_operandCallback[0], self::OPERAND_TYPE_FUNCTION, $name, array(), $this->m_operandCallback[1]);
            }
        }else{
            $temp = strtolower($evaltext);
            if($temp == 'true')
                return true;
            else if($temp == 'false')
                return false;
            if(strlen($evaltext) >= 2) {
                $a = substr($evaltext, 0, 1);
                $b = substr($evaltext, -1, 1);
                if ($a == '\'' && $b == '\'')
                    return substr($evaltext, 1, strlen($evaltext) - 2);
            }
            return call_user_func($this->m_operandCallback[0], self::OPERAND_TYPE_USERDEFINED, $evaltext, NULL, $this->m_operandCallback[1]);
        }
    }

    private function calculate($state_depth, $opn1, $opn2, $opr, &$output)
    {
        if(!$opn1) $opn1 = 0;
        if(!$opn2) $opn2 = 0;

        if(!is_numeric($opn1) && !is_bool($opn1))
        {
            $opn1 = $this->myEval($opn1);
        }
        if(!is_numeric($opn2) && !is_bool($opn2))
        {
            $opn2 = $this->myEval($opn2);
        }

        switch($opr) {
            case '+':
                $opn1 = $opn1 + $opn2;
                break;
            case '-' :
                $opn1 = $opn2 - $opn1;
                break;
            case '*' :
                $opn1 = $opn1 * $opn2;
                break;
            case '/' :
                $opn1 = $opn2 / $opn1;
                break;
            case '||' :
                $opn1 = $opn2 || $opn1;
                break;
            case '&&' :
                $opn1 = $opn2 && $opn1;
                break;
            case '|' :
                $opn1 = $opn2 | $opn1;
                break;
            case '&' :
                $opn1 = $opn2 & $opn1;
                break;
        }
        $output = $opn1;
        return 1;
    }

    public function parse($buf)
    {
        $this->init();

        $prev = 0;

        $arrtokens = array(
            1 => '(',
            2 => ')',
            3 => '||',
            4 => '&&',
            5 => '+',
            6 => '-',
            7 => '/',
            8 => '*',
            10 => '|',
            11 => '&',
        );

        $len = strlen($buf);
        $state_depth = 0;
        for($i=0; $i<$len;$i+=$curlen)
        {
            $curlen = 1;
            if($buf[$i] == ' ')
                continue;
            foreach($arrtokens as $tidx => $tpattern)
            {
                $j = strlen($tpattern);
                $a = substr($buf, $i, $j);

                if(strcmp($a, $tpattern) == 0)
                {
                    $foundlen = $i - $prev;

                    if($tidx == 1)
                    {
                        $frontpadding = 0;
                        //$frontpadding = $j;
                        $tlen = strlen($buf);
                        for($k=$prev + $frontpadding; $k < $tlen; $k++)
                        {
                            if($buf[$k] == ' ')
                            {
                                $frontpadding++;
                            }else{
                                break;
                            }
                        }
                        $temp = substr($buf, $prev + $frontpadding);
                        $matches = NULL;
                        $findrst = preg_match('/^(([A-Za-z0-9]+)\\([^\\)]*\\))/', $temp, $matches);
                        if($findrst > 0)
                        {
                            $curlen = 0;
                            $i = $prev + $frontpadding + strlen($matches[0]);
                            continue;
                        }
                    }

                    if($foundlen > 0) {
                        $operand = trim(substr($buf, $prev, $foundlen)); // trim

                        if (strlen($operand) > 0) {
                            $this->operandPush($operand);
                        }
                    }

                    $prev = $i + $j;

                    if($tidx == 1) {
                        $this->operatorPush($tpattern);
                        $state_depth++;
                    }else if($tidx == 2) {
                        do{
                            $tmpopr = $this->operatorPop();
                            if($tmpopr != '('){
                                $opn2 = $this->operandPop();
                                $opn1 = $this->operandPop();

                                $tmpoutput = 0;
                                $state_persist = $this->calculate($state_depth, $opn1, $opn2 , $tmpopr, $tmpoutput);
                                if($state_depth == 0 && $state_persist == 0)
                                    return 0;
                                $this->operandPush($tmpoutput);
                            }
                        }while($tmpopr != '(');
                        $state_depth--;
                    }else{
                        if ($this->isEmptyOperatorStack())
                            $this->operatorPush($tpattern);
                        else {
                            $opr = $this->operatorPop();
                            if ($this->greaterOpr($opr, $tpattern)) {
                                $opn2 = $this->operandPop();
                                $opn1 = $this->operandPop();

                                $tmpoutput = 0;
                                $state_persist = $this->calculate($state_depth, $opn1, $opn2, $opr, $tmpoutput);
                                if($state_depth == 0 && $state_persist == 0)
                                    return 0;
                                $this->operandPush($tmpoutput);
                                $this->operatorPush($tpattern);
                            } else {
                                $this->operatorPush($opr);
                                $this->operatorPush($tpattern);
                            }
                        }
                    }

                    $curlen = $j;
                    break;
                }
            }
        }

        $operand = trim(substr($buf, $prev));
        if(strlen($operand) > 0) {
            $this->operandPush($operand);
        }
        while(!$this->isEmptyOperatorStack()) {
            $opn1 = $this->operandPop();
            $opn2 = $this->operandPop();
            $opr = $this->operatorPop();

            $output = 0;

            $state_persist = $this->calculate($state_depth, $opn1, $opn2, $opr, $output);
            $this->operandPush($output);
        }

        if($this->isEmptyOperatorStack() && !$this->isEmptyOperandStack())
        {
            $opn = $this->operandPop();
            $output = 0;

            if(!is_numeric($opn) && !is_bool($opn))
            {
                $opn = $this->myEval($opn);
            }

            $this->operandPush($opn);
        }

        return $this->operandPop();
    }
}
