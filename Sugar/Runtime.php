<?php
/****************************************************************************
PHP-Sugar
Copyright (c) 2007  AwesomePlay Productions, Inc. and
contributors.  All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

 * Redistributions of source code must retain the above copyright notice,
   this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in the
   documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
ARE DISCLAIMED. IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
DAMAGE.
****************************************************************************/

class SugarRuntime {
    public static function showValue (&$value) {
        if (is_bool($value))
            return $value?'true':'false';
        elseif (is_array($value))
            return print_r($value, true);
        else
            return $value;
    }

    public static function addValues ($left, $right) {
        if (is_numeric($left) && is_numeric($right))
            return $left + $right;
        elseif (is_array($left) && is_array($rihgt))
            return array_merge($left, $right);
        else
            return $left . $right;
    }

    public static function invoke (&$sugar, $invoke, $flags, $args) {
        // exception net
        try {
            // call function, using appropriate method
            if ($flags & SUGAR_FUNC_NATIVE)
                return call_user_func_array($invoke, $args);
            else
                return call_user_func($invoke, $sugar, $args);
        } catch (Exception $e) {
            $sugar->handleError($e);
            return null;
        }
    }

    public static function execute (&$sugar, $code) {
        $stack = array();

        for ($i = 0; $i < count($code); ++$i) {
            switch($code[$i]) {
                case 'echo':
                    if ($sugar->cacheHandler)
                        $sugar->cacheHandler->addOutput($code[++$i]);
                    else
                        echo $code[++$i];
                    break;
                case 'print':
                    $val = array_pop($stack);
                    if ($sugar->cacheHandler)
                        $sugar->cacheHandler->addOutput($sugar->escape(SugarRuntime::showValue($val)));
                    else
                        echo $sugar->escape(SugarRuntime::showValue($val));
                    break;
                case 'push':
                    $str = $code[++$i];
                    $stack []= $str;
                    break;
                case 'lookup':
                    $var = strtolower($code[++$i]);
                    $stack []= $sugar->getVariable($var);
                    break;
                case 'assign':
                    $name = $code[++$i];
                    $value = array_pop($stack);
                    $sugar->set($name, $value);
                    break;
                case 'negate':
                    $v = array_pop($stack);
                    $stack []= -$v;
                    break;
                case '!':
                    $v = array_pop($stack);
                    $stack []= !$v;
                    break;
                case '..':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    $stack []= $v1 . $v2;
                    break;
                case '+':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    $stack []= SugarRuntime::addValues($v1, $v2);
                    break;
                case '*':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    $stack []= $v1 * $v2;
                    break;
                case '-':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    $stack []= $v1 - $v2;
                    break;
                case '/':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    if ($v2 == 0)
                        $stack []= null;
                    else
                        $stack []= $v1 / $v2;
                    break;
                case '%':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    if ($v2 == 0)
                        $stack []= null;
                    else
                        $stack []= $v1 % $v2;
                    break;
                case '==':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    $stack []= ($v1 == $v2);
                    break;
                case '!=':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    $stack []= ($v1 != $v2);
                    break;
                case '||':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    $stack []= ($v1 || $v2);
                    break;
                case '&&':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    $stack []= ($v1 && $v2);
                    break;
                case '<':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    $stack []= ($v1 < $v2);
                    break;
                case '<=':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    $stack []= ($v1 <= $v2);
                    break;
                case '>':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    $stack []= ($v1 > $v2);
                    break;
                case '>=':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    $stack []= ($v1 >= $v2);
                    break;
                case 'in':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    $stack []= (is_array($v2) && in_array($v1, $v2));
                    break;
                case 'call':
                    $func = $code[++$i];
                    $args = $code[++$i];

                    // lookup function
                    $invoke =& $sugar->getFunction($func);
                    if (!$invoke)
                        throw new SugarException ('unknown function: '.$func);

                    // compile args
                    $params = array();
                    foreach($args as $name=>$pcode)
                        $params[$name] = SugarRuntime::execute($sugar, $pcode);

                    // exception net
                    $ret = SugarRuntime::invoke($sugar, $invoke[0], $invoke[1], $params);

                    // store return value
                    $stack []= $ret;
                    break;
                case 'method':
                    $obj = array_pop($stack);
                    $func = $code[++$i];
                    $args = $code[++$i];

                    if (!$sugar->methods)
                        throw new SugarException ('method invocation is disabled');

                    if (!is_object($obj))
                        throw new SugarException ('method call on non-object');

                    if (!method_exists($obj, $func))
                        throw new SugarException ('unknown method on object: '.$func);

                    // compile args
                    $params = array();
                    foreach($args as $pcode)
                        $params [] = SugarRuntime::execute($sugar, $pcode);

                    // invoke
                    $stack []= SugarRuntime::invoke($sugar, array($obj, $func), SUGAR_FUNC_NATIVE, $params);
                    break;
                case 'if':
                    $test = array_pop($stack);
                    $true = $code[++$i];
                    $false = $code[++$i];
                    if ($test && $true)
                        SugarRuntime::execute($sugar, $true);
                    elseif (!$test && $false)
                        SugarRuntime::execute($sugar, $false);
                    break;
                case 'range':
                    $step = array_pop($stack);
                    $upper = array_pop($stack);
                    $lower = array_pop($stack);
                    $name = $code[++$i];
                    $block = $code[++$i];

                    // if step is 0, fail
                    if ($step == 0)
                        throw new SugarException ('step of 0 in range loop');

                    // iterate
                    $index = $lower;
                    while (($step < 0 && $index >= $upper) || ($step > 0 && $index <= $upper)) {
                        $sugar->set($name, $index);
                        SugarRuntime::execute($sugar, $block);
                        $index += $step;
                    }
                    break;
                case 'foreach':
                    $array = array_pop($stack);
                    $key = $code[++$i];
                    $name = $code[++$i];
                    $block = $code[++$i];
                    if (is_array($array) || is_object($array)) {
                        foreach($array as $k=>$v) {
                            if ($key)
                                $sugar->set($key, $k);
                            $sugar->set($name, $v);
                            SugarRuntime::execute($sugar, $block);
                        }
                    }
                    break;
                case 'while':
                    $test = $code[++$i];
                    $block = $code[++$i];
                    while (SugarRuntime::execute($sugar, $test))
                        SugarRuntime::execute($sugar, $block);
                    break;
                case 'nocache':
                    $block = $code[++$i];
                    if ($sugar->cacheHandler)
                        $sugar->cacheHandler->addBlock($block);
                    else
                        SugarRuntime::execute($sugar, $block);
                    break;
                case '.':
                    $index = array_pop($stack);
                    $array = array_pop($stack);
                    if (is_array($array))
                        $stack []= $array[$index];
                    else
                        $stack []= null;
                    break;
                case '->':
                    $prop = array_pop($stack);
                    $obj = array_pop($stack);
                    if (is_object($obj))
                        $stack []= $obj->$prop;
                    break;
                case 'array':
                    $elems = $code[++$i];
                    $array = array();
                    foreach ($elems as $elem)
                        $array []= SugarRuntime::execute($sugar, $elem);
                    $stack []= $array;
                    break;
                default:
                    throw new SugarException ('unknown opcode: '.$code[$i]);
            }
        }

        return $stack[0];
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
