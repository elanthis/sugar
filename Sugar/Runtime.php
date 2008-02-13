<?php
/**
 * PHP-Sugar Template Engine
 *
 * Copyright (c) 2007  AwesomePlay Productions, Inc. and
 * contributors.  All rights reserved.
 *
 * LICENSE:
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package Sugar
 * @subpackage Internals
 * @author Sean Middleditch <sean@awesomeplay.com>
 * @copyright 2007 AwesomePlay Productions, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

/**
 * This class is a namespace containing static function relevant to 
 * executing Sugar bytecode.
 *
 * @package Sugar
 * @subpackage Internals
 */
class SugarRuntime {
    /**
     * Converts a PHP value into something nice for a user to see.  Mainly
     * this is intended for arrays, objects, and boolean values, which are
     * not natively user-visible formats.
     *
     * @param mixed $value Value to convert.
     * @return string User-visible rendition of the value.
     */
    public static function showValue ($value) {
        if (is_bool($value))
            return $value?'true':'false';
        elseif (is_array($value))
            return SugarUtil::jsValue($value);
        else
            return $value;
    }

    /**
     * Attempts to add two PHP values together.  If both types are, this
     * performs a regular addition.  If both types are arrays, this
     * performs an array_merge() on the arrays.  Otherwise, both values
     * are concatenated with the dot operator.
     *
     * @param mixed $left The left-hand operand to add.
     * @param mixed $right The right-hand operand to add.
     * @return mixed The result of the addition.
     */
    public static function addValues ($left, $right) {
        if (is_numeric($left) && is_numeric($right))
            return $left + $right;
        elseif (is_array($left) && is_array($rihgt))
            return array_merge($left, $right);
        else
            return $left . $right;
    }

    /**
     * Executes the given bytecode.  The return value is the last item on
     * the stack, if any.  For complete templates, this should be nothing
     * (null).
     *
     * @param Sugar $sugar Sugar instance.
     * @param array $code Bytecode to execute.
     * @return mixed Last value on stack.
     */
    public static function execute ($sugar, $code) {
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
                case '!in':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    $stack []= (is_array($v2) && !in_array($v1, $v2));
                    break;
                case 'call':
                    $func = $code[++$i];
                    $args = $code[++$i];
                    $debug_file = $code[++$i];
                    $debug_line = $code[++$i];

                    // lookup function
                    $invoke = $sugar->getFunction($func);
                    if (!$invoke)
                        throw new SugarRuntimeException($debug_file, $debug_line, 'unknown function `'.$func.'`');

                    // compile args
                    $params = array();
                    foreach($args as $name=>$pcode)
                        $params[$name] = SugarRuntime::execute($sugar, $pcode);

										// exception net
										try {
												// call function, using appropriate method
												$ret = call_user_func($invoke, $sugar, $params);
										} catch (Exception $e) {
												$sugar->handleError($e);
												$ret = null;
										}

                    // store return value
                    $stack []= $ret;
                    break;
                case 'method':
                    $obj = array_pop($stack);
                    $func = $code[++$i];
                    $args = $code[++$i];
                    $debug_file = $code[++$i];
                    $debug_line = $code[++$i];

                    if (!$sugar->methods)
                        throw new SugarRuntimeException($debug_file, $debug_line, 'method invocation disabled');

                    if (!is_object($obj))
                        throw new SugarRuntimeException($debug_file, $debug_line, 'method call on non-object type `'.gettype($obj).'`');

                    if (!method_exists($obj, $func))
                        throw new SugarRuntimeException($debug_file, $debug_line, 'unknown method `'.$func.'` on type `'.gettype($obj).'`');


                    // compile args
                    $params = array();
                    foreach($args as $pcode)
                        $params [] = SugarRuntime::execute($sugar, $pcode);

										// exception net
										try {
												// invoke method
												$stack []= @call_user_func_array(array($obj, $func), $params);
										} catch (Exception $e) {
												$sugar->handleError($e);
												$stack []= null;
										}
                    break;
                case 'if':
                    $clauses = $code[++$i];
                    foreach ($clauses as $clause) {
                        if ($clause[0] === false || SugarRuntime::execute($sugar, $clause[0])) {
                            SugarRuntime::execute($sugar, $clause[1]);
                            break;
                        }
                    }
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
                    if (is_array($array) && isset($array[$index]))
                        $stack []= $array[$index];
                    else
                        $stack []= null;
                    break;
                case '->':
                    $prop = array_pop($stack);
                    $obj = array_pop($stack);
                    if (is_object($obj) && isset($obj->$prop))
                        $stack []= $obj->$prop;
                    else
                        $stack []= null;
                    break;
                case 'array':
                    $elems = $code[++$i];
                    $array = array();
                    foreach ($elems as $elem)
                        $array []= SugarRuntime::execute($sugar, $elem);
                    $stack []= $array;
                    break;
                default:
                    throw new SugarException ('internal error: unknown opcode `'.$code[$i].'`');
            }
        }

        return isset($stack[0]) ? $stack[0] : false;
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
