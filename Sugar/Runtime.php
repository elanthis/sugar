<?php
/**
 * Runtime engine.
 *
 * This is the runtime engine, which takes the code compiled by SugarParser
 * and makes it go.  It handles the various operators, output escaping,
 * caching, and method/function invocation with exception safety nets.
 *
 * The design is not particularly clever or efficient, and could use a could
 * round of profiling and improvement.  Parsing only gets called once every
 * time a template is modified, but the runtime is invoked for every single
 * page display, even for cached pages.
 *
 * PHP version 5
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
 * @category   Template
 * @package    Sugar
 * @subpackage Runtime
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2008-2009 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    SVN: $Id$
 * @link       http://php-sugar.net
 * @access     private
 */

/**
 * Namespace for the Sugar runtime engine functions.
 *
 * This class is a namespace containing static function relevant to 
 * executing Sugar bytecode.
 *
 * @category   Template
 * @package    Sugar
 * @subpackage Runtime
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2008-2009 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    Release: 0.83
 * @link       http://php-sugar.net
 * @access     private
 */
class SugarRuntime
{
    /**
     * Converts a PHP value into something nice for a user to see.  Mainly
     * this is intended for arrays, objects, and boolean values, which are
     * not natively user-visible formats.
     *
     * @param mixed $value Value to convert.
     *
     * @return string User-visible rendition of the value.
     */
    public static function showValue($value)
    {
        if (is_bool($value)) {
            return $value?'true':'false';
        } elseif (is_array($value)) {
            return Sugar_Util_Json($value);
        } else {
            return $value;
        }
    }

    /**
     * Attempts to add two PHP values together.  If both types are, this
     * performs a regular addition.  If both types are arrays, this
     * performs an array_merge() on the arrays.  Otherwise, both values
     * are concatenated with the dot operator.
     *
     * @param mixed $left  The left-hand operand to add.
     * @param mixed $right The right-hand operand to add.
     *
     * @return mixed The result of the addition.
     */
    public static function addValues($left, $right)
    {
        if (is_numeric($left) && is_numeric($right)) {
            return $left + $right;
        } elseif (is_array($left) && is_array($right)) {
            return array_merge($left, $right);
        } else {
            return $left . $right;
        }
    }

    /**
     * Display output, either to the cache handler or to the PHP
     * output stream.
     *
     * @param Sugar  $sugar  Sugar object.
     * @param string $output Output.
     *
     * @return bool True on success.
     */
    private static function _display(Sugar $sugar, $output)
    {
        if ($sugar->cacheHandler) {
            return $sugar->cacheHandler->addOutput($output);
        } else {
            echo $output;
            return true;
        }
    }

    /**
     * Executes the given bytecode.  The return value is the last item on
     * the stack, if any.  For complete templates, this should be nothing
     * (null).
     *
     * @param Sugar $sugar    Sugar instance.
     * @param array $code     Bytecode to execute.
     * @param array $sections Section bytecodes.
     *
     * @return mixed Last value on stack.
     * @throws Sugar_Exception_Runtime when the user has provided code that
     * cannot be executed, such as attempting to call a function that does
     * not exist.
     */
    public static function execute($sugar, $code, $sections)
    {
        $stack = array();

        for ($i = 0; $i < count($code); ++$i) {
            $opcode = $code[$i];
            switch($opcode) {
            case 'echo':
                self::_display($sugar, $code[++$i]);
                break;
            case 'print':
                $val = array_pop($stack);
                self::_display($sugar, $sugar->escape(self::showValue($val)));
                break;
            case 'rawprint':
                $val = array_pop($stack);
                self::_display($sugar, self::showValue($val));
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
            case 'insert':
                $name = $code[++$i];
                if (isset($sections[$name])) {
                    self::execute($sugar, $sections[$name], $sections);
                } else {
                    throw new Sugar_Exception_Runtime(
                        $debug_file,
                        $debug_line,
                        'unknown section `'.$name.'`'
                    );
                }
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
                $stack []= self::addValues($v1, $v2);
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
                if ($v2 == 0) {
                    $stack []= null;
                } else {
                    $stack []= $v1 / $v2;
                }
                break;
            case '%':
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                if ($v2 == 0) {
                    $stack []= null;
                } else {
                    $stack []= $v1 % $v2;
                }
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
            case 'call_top':
                $func = $code[++$i];
                $args = $code[++$i];
                $escape_flag = $opcode == 'call_top' ? $code[++$i] : false;
                $debug_file = $code[++$i];
                $debug_line = $code[++$i];

                // lookup function
                $callable = $sugar->getFunction($func);
                if (!$callable) {
                    throw new Sugar_Exception_Runtime(
                        $debug_file,
                        $debug_line,
                        'unknown function `'.$func.'`'
                    );
                }

                // update escape flag based on function default
                $escape_flag = $escape_flag && $callable['escape'];

                // compile args
                $params = array();
                foreach ($args as $name=>$pcode) {
                    $params[$name] = self::execute($sugar, $pcode, $sections);
                }

                // exception net
                try {
                    // call function, using appropriate method
                    $ret = call_user_func($callable['invoke'], $sugar, $params);
                } catch (Exception $e) {
                    $sugar->handleError($e);
                    $ret = null;
                }

                // process return value
                if ($opcode == 'call_top' && $escape_flag) {
                    self::_display($sugar, $sugar->escape(self::showValue($ret)));
                } elseif ($opcode == 'call_top') {
                    self::_display($sugar, self::showValue($ret));
                } else {
                    $stack []= $ret;
                }
                break;
            case 'method':
                $obj = array_pop($stack);
                $func = $code[++$i];
                $args = $code[++$i];
                $debug_file = $code[++$i];
                $debug_line = $code[++$i];

                // ensure the object is an object and that the method is a method
                if (!is_object($obj)) {
                    throw new Sugar_Exception_Runtime(
                        $debug_file,
                        $debug_line,
                        'method call on non-object type `'.gettype($obj).'`'
                    );
                }

                if (!method_exists($obj, $func)) {
                    throw new Sugar_Exception_Runtime(
                        $debug_file,
                        $debug_line,
                        'unknown method `'.$func.'` on type `'.gettype($obj).'`'
                    );
                }

                // compile args
                $params = array();
                foreach ($args as $pcode) {
                    $params [] = self::execute($sugar, $pcode, $sections);
                }

                // perform ACL checking on the method call
                if (!is_null($sugar->method_acl)) {
                    $check = call_user_func(
                        $sugar->method_acl,
                        $sugar,
                        $obj,
                        $func,
                        $params
                    );

                    if (!$check) {
                        throw new Sugar_Exception_Runtime(
                            $debug_file,
                            $debug_line,
                            'method call to `'.$func.'` on type `'.
                                gettype($obj).'` blocked by ACL'
                        );
                    }
                }

                // exception net
                try {
                    // invoke method
                    $stack []= @call_user_func_array(array($obj, $func), $params);
                } catch (Exception $e) {
                    $sugar->handleError($e);
                    $stack []= null;
                }
                break;
            case 'modifier':
                $name = $code[++$i];
                $args = $code[++$i];
                $value = array_pop($stack);

                // lookup function
                $callable = $sugar->getModifier($name);
                if (!$callable) {
                    throw new Sugar_Exception_Runtime(
                        'FIXME',
                        1,
                        'unknown modifier `'.$name.'`'
                    );
                }

                // compile args
                $params = array();
                foreach ($args as $pcode) {
                    $params []= self::execute($sugar, $pcode, $sections);
                }

                // exception net
                try {
                    // invoke the modifier
                    $ret = call_user_func($callable, $value, $sugar, $params);
                } catch (Exception $e) {
                    $sugar->handleError($e);
                    $ret = null;
                }

                // store return value
                $stack []= $ret;
                break;
            case 'if':
                $clauses = $code[++$i];
                foreach ($clauses as $clause) {
                    if ($clause[0] === false || self::execute($sugar, $clause[0], $sections)) {
                        self::execute($sugar, $clause[1], $sections);
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
                if ($step === 0) {
                    throw new Sugar_Exception ('step of 0 in range loop');
                }

                // iterate
                $index = $lower;
                while (($step < 0 && $index >= $upper)
                    || ($step > 0 && $index <= $upper)
                ) {
                    $sugar->set($name, $index);
                    self::execute($sugar, $block, $sections);
                    $index += $step;
                }
                break;
            case 'foreach':
                $array = array_pop($stack);
                $key = $code[++$i];
                $name = $code[++$i];
                $block = $code[++$i];
                if (is_array($array) || is_object($array)) {
                    foreach ($array as $k=>$v) {
                        if ($key) {
                            $sugar->set($key, $k);
                        }
                        $sugar->set($name, $v);
                        self::execute($sugar, $block, $sections);
                    }
                }
                break;
            case 'while':
                $test = $code[++$i];
                $block = $code[++$i];
                while (self::execute($sugar, $test, $sections)) {
                    self::execute($sugar, $block, $sections);
                }
                break;
            case 'nocache':
                $block = $code[++$i];
                if ($sugar->cacheHandler) {
                    $sugar->cacheHandler->addBlock($block);
                } else {
                    self::execute($sugar, $block, $sections);
                }
                break;
            case '.':
                $index = array_pop($stack);
                $obj = array_pop($stack);
                if (is_array($obj) && isset($obj[$index])) {
                    $stack []= $obj[$index];
                } elseif (is_object($obj) && isset($obj->$index)) {
                    $stack []= $obj->$index;
                } else {
                    $stack []= null;
                }
                break;
            case 'array':
                $elems = $code[++$i];
                $array = array();
                foreach ($elems as $elem) {
                    $array []= self::execute($sugar, $elem, $sections);
                }
                $stack []= $array;
                break;
            default:
                throw new Sugar_Exception(
                    'internal error: unknown opcode `'.$opcode.'`'
                );
            }
        }

        return end($stack);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
