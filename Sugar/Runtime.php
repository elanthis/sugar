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
 * Sugar runtime engine.
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
final class Sugar_Runtime {
    /**@#+*/
    /** Opcodes */
    const OP_LPRINT = 1;
    const OP_EPRINT = 2;
    const OP_RPRINT = 3;
    const OP_PUSH = 4;
    const OP_LOOKUP = 5;
    const OP_ASSIGN = 6;
    const OP_INSERT = 7;
    const OP_NEGATE = 8;
    const OP_NOT = 9;
    const OP_CONCAT = 10;
    const OP_ADD = 11;
    const OP_MULTIPLY = 12;
    const OP_SUBTRACT = 13;
    const OP_DIVIDE = 14;
    const OP_MODULUS = 15;
    const OP_EQ = 16;
    const OP_NE = 17;
    const OP_OR = 18;
    const OP_AND = 19;
    const OP_LT = 20;
    const OP_LTE = 21;
    const OP_GT = 22;
    const OP_GTE = 23;
    const OP_IN = 24;
    const OP_NOT_IN = 25;
    const OP_CALL = 26;
    const OP_METHOD = 27;
    const OP_MODIFY = 28;
    const OP_IF = 29;
    const OP_RANGE = 30;
    const OP_FOREACH = 31;
    const OP_WHILE = 32;
    const OP_NOCACHE = 33;
    const OP_DEREF = 34;
    const OP_MAKE_ARRAY = 35;
    /**@#-*/

    /**
     * Sugar instance
     *
     * @var Sugar
     */
    private $_sugar;

    /**
     * Cache handler.
     *
     * @var Sugar_Cache
     */
    private $_cache;

    /**
     * Constructor
     *
     * @param Sugar       $sugar Sugar Instance
     */
    public function __construct(Sugar $sugar)
    {
        $this->_sugar = $sugar;
    }

    /**
     * Converts a PHP value into something nice for a user to see.  Mainly
     * this is intended for arrays, objects, and boolean values, which are
     * not natively user-visible formats.
     *
     * @param mixed $value Value to convert.
     *
     * @return string User-visible rendition of the value.
     */
    private static function _valueToString($value)
    {
        if (is_bool($value)) {
            return $value?'true':'false';
        } elseif (is_array($value)) {
            return Sugar_Util_Json($value);
        } else {
            return (string)$value;
        }
    }

    /**
     * Display output, either to the cache handler or to the PHP
     * output stream.
     *
     * @param Sugar_Cache $cache Cache handler (or null for no caching).
     * @param string             $output       Output.
     *
     * @return bool True on success.
     */
    private static function _display($cache, $output)
    {
        if ($cache) {
            return $cache->addOutput($output);
        } else {
            echo $output;
            return true;
        }
    }

    /**
     * Executes the given bytecode.
     *
     * @param Sugar_Context  $context Context to execute with
     * @param Sugar          $sugar   Sugar instance
     * @param Sugar_Data     $date    Variable data
     * @param Sugar_Cache    $cache   Caching handler
     * @param Sugar_Compiled $code    Compiled template being executed
     * @param array          $opcodes Opcodes being executed
     * @param array          $code    Bytecode to execute.
     * @param array          $stack   Stack
     *
     * @return mixed Last value on stack.
     * @throws Sugar_Exception_Runtime when the user has provided code that
     * cannot be executed, such as attempting to call a function that does
     * not exist.
     */
    private static function _execute(Sugar_Context $context, Sugar $sugar,
        Sugar_Data $data, $cache, Sugar_Compiled $code, array $opcodes,
        array &$stack
    ) {
        // execute opcodes
        for ($i = 0; $i < count($opcodes); ++$i) {
            // get current opcode
            $opcode = $opcodes[$i];
            switch($opcode) {
            case Sugar_Runtime::OP_LPRINT:
                self::_display($cache, $opcodes[++$i]);
                break;
            case Sugar_Runtime::OP_EPRINT:
                $v1 = array_pop($stack);
                self::_display($cache, $sugar->escape(self::_valueToString($v1)));
                break;
            case Sugar_Runtime::OP_RPRINT:
                $v1 = array_pop($stack);
                self::_display($cache, self::_valueToString($v1));
                break;
            case Sugar_Runtime::OP_PUSH:
                $v1 = $opcodes[++$i];
                $stack []= $v1;
                break;
            case Sugar_Runtime::OP_LOOKUP:
                $name = strtolower($opcodes[++$i]);
                $stack []= $data->get($name);
                break;
            case Sugar_Runtime::OP_ASSIGN:
                $name = $opcodes[++$i];
                $v1 = array_pop($stack);
                $data->set($name, $v1);
                break;
            case Sugar_Runtime::OP_INSERT:
                $name = $opcodes[++$i];
                $section = $code->getSection($name);
                if ($section !== false) {
                    self::_execute($context, $sugar, $data, $cache, $code, $section, $stack);
                } else {
                    throw new Sugar_Exception_Runtime(
                        $debug_file,
                        $debug_line,
                        'unknown section `'.$name.'`'
                    );
                }
                break;
            case Sugar_Runtime::OP_NEGATE:
                $v1 = array_pop($stack);
                $stack []= -$v1;
                break;
            case Sugar_Runtime::OP_NOT:
                $v1 = array_pop($stack);
                $stack []= !$v1;
                break;
            case Sugar_Runtime::OP_CONCAT:
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                $stack []= $v1 . $v2;
                break;
            case Sugar_Runtime::OP_ADD:
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                if (is_numeric($v1) && is_numeric($v2)) {
                    $stack []= $v1 + $v2;
                } elseif (is_array($v1) && is_array($v2)) {
                    $stack []= array_merge($v1, $v2);
                } else {
                    $stack []= $v1 . $v2;
                }
                break;
            case Sugar_Runtime::OP_MULTIPLY:
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                $stack []= $v1 * $v2;
                break;
            case Sugar_Runtime::OP_SUBTRACT:
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                $stack []= $v1 - $v2;
                break;
            case Sugar_Runtime::OP_DIVIDE:
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                if ($v2 == 0) {
                    $stack []= null;
                } else {
                    $stack []= $v1 / $v2;
                }
                break;
            case Sugar_Runtime::OP_MODULUS:
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                if ($v2 == 0) {
                    $stack []= null;
                } else {
                    $stack []= $v1 % $v2;
                }
                break;
            case Sugar_Runtime::OP_EQ:
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                $stack []= ($v1 == $v2);
                break;
            case Sugar_Runtime::OP_NE:
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                $stack []= ($v1 != $v2);
                break;
            case Sugar_Runtime::OP_OR:
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                $stack []= ($v1 || $v2);
                break;
            case Sugar_Runtime::OP_AND:
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                $stack []= ($v1 && $v2);
                break;
            case Sugar_Runtime::OP_LT:
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                $stack []= ($v1 < $v2);
                break;
            case Sugar_Runtime::OP_LTE:
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                $stack []= ($v1 <= $v2);
                break;
            case Sugar_Runtime::OP_GT:
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                $stack []= ($v1 > $v2);
                break;
            case Sugar_Runtime::OP_GTE:
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                $stack []= ($v1 >= $v2);
                break;
            case Sugar_Runtime::OP_IN:
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                $stack []= (is_array($v2) && in_array($v1, $v2));
                break;
            case Sugar_Runtime::OP_NOT_IN:
                $v2 = array_pop($stack);
                $v1 = array_pop($stack);
                $stack []= (is_array($v2) && !in_array($v1, $v2));
                break;
            case Sugar_Runtime::OP_CALL:
                $func = $opcodes[++$i];
                $count = $opcodes[++$i];
                $debug_file = $opcodes[++$i];
                $debug_line = $opcodes[++$i];

                // lookup function
                $plugin = $sugar->getPlugin('function', $func);
                if (!$plugin) {
                    throw new Sugar_Exception_Runtime(
                        $debug_file,
                        $debug_line,
                        'unknown function `'.$func.'`'
                    );
                }

                // build array
                $params = array();
                $start = count($stack) - $count * 2;
                for ($index = $start; $index != count($stack); $index += 2) {
                    $params [$stack[$index]]= $stack[$index + 1];
                }

                // remove elements from stack (FIXME: yuck, this is silly)
                while (count($stack) != $start) {
                    array_pop($stack);
                }

                // exception net
                try {
                    // call function, using appropriate method
                    $ret = $plugin->invoke($params, $context);
                } catch (Exception $e) {
                    $sugar->handleError($e);
                    $ret = null;
                }

                // process return value
                $stack []= $ret;
                break;
            case Sugar_Runtime::OP_METHOD:
                $name = $opcodes[++$i];
                $count = $opcodes[++$i];
                $debug_file = $opcodes[++$i];
                $debug_line = $opcodes[++$i];

                // get object
                $obj = $stack[count($stack) - $count - 1];

                // ensure the object is an object and that the method is a method
                if (!is_object($obj)) {
                    throw new Sugar_Exception_Runtime(
                        $debug_file,
                        $debug_line,
                        'method call on non-object type `'.gettype($obj).'`'
                    );
                }

                if (!method_exists($obj, $name)) {
                    throw new Sugar_Exception_Runtime(
                        $debug_file,
                        $debug_line,
                        'unknown method `'.$name.'` on type `'.gettype($obj).'`'
                    );
                }

                // pull params out of stack
                $start = count($stack) - $count;
                $params = array_slice($stack, $start, $count);

                // remove elements from stack (FIXME: yuck, this is silly)
                while (count($stack) != $start - 1) {
                    array_pop($stack);
                }

                // perform ACL checking on the method call
                if (!is_null($sugar->methodAcl)) {
                    $check = call_user_func(
                        $sugar->methodAcl,
                        $sugar,
                        $obj,
                        $name,
                        $params,
                        $context
                    );

                    if (!$check) {
                        throw new Sugar_Exception_Runtime(
                            $debug_file,
                            $debug_line,
                            'method call to `'.$name.'` on type `'.
                                gettype($obj).'` blocked by ACL'
                        );
                    }
                }

                // exception net
                try {
                    // invoke method
                    $stack []= @call_user_func_array(array($obj, $name), $params);
                } catch (Exception $e) {
                    $sugar->handleError($e);
                    $stack []= null;
                }
                break;
            case Sugar_Runtime::OP_MODIFY:
                $name = $opcodes[++$i];
                $args = $opcodes[++$i];
                $value = array_pop($stack);

                // lookup function
                $plugin = $sugar->getPlugin('modifier', $name);
                if (!$plugin) {
                    throw new Sugar_Exception_Runtime(
                        'FIXME',
                        1,
                        'unknown modifier `'.$name.'`'
                    );
                }

                // compile args
                $params = array();
                foreach ($args as $pcode) {
                    $params []= self::_execute($context, $sugar, $data, $cache, $code, $pcode, $stack);
                }

                // exception net
                try {
                    // invoke the modifier
                    $ret = $plugin->invoke($value, $params, $context);
                } catch (Exception $e) {
                    $sugar->handleError($e);
                    $ret = null;
                }

                // store return value
                $stack []= $ret;
                break;
            case Sugar_Runtime::OP_IF:
                $clauses = $opcodes[++$i];
                foreach ($clauses as $clause) {
                    if ($clause[0] === false || self::_execute($context, $sugar, $data, $cache, $code, $clause[0], $stack)) {
                        self::_execute($context, $sugar, $data, $cache, $code, $clause[1], $stack);
                        break;
                    }
                }
                break;
            case Sugar_Runtime::OP_RANGE:
                $step = array_pop($stack);
                $upper = array_pop($stack);
                $lower = array_pop($stack);
                $name = $opcodes[++$i];
                $block = $opcodes[++$i];

                // if step is 0, fail
                if ($step === 0) {
                    throw new Sugar_Exception ('step of 0 in range loop');
                }

                // iterate
                $index = $lower;
                while (($step < 0 && $index >= $upper)
                    || ($step > 0 && $index <= $upper)
                ) {
                    $data->set($name, $index);
                    self::_execute($context, $sugar, $data, $cache, $code, $block, $stack);
                    $index += $step;
                }
                break;
            case Sugar_Runtime::OP_FOREACH:
                $array = array_pop($stack);
                $key = $opcodes[++$i];
                $name = $opcodes[++$i];
                $block = $opcodes[++$i];
                if (is_array($array) || is_object($array)) {
                    foreach ($array as $k=>$v) {
                        if ($key) {
                            $data->set($key, $k);
                        }
                        $data->set($name, $v);
                        self::_execute($context, $sugar, $data, $cache, $code, $block, $stack);
                    }
                }
                break;
            case Sugar_Runtime::OP_WHILE:
                $test = $opcodes[++$i];
                $block = $opcodes[++$i];
                while (self::_execute($context, $sugar, $data, $cache, $code, $test, $stack)) {
                    self::_execute($context, $sugar, $data, $cache, $code, $block, $stack);
                }
                break;
            case Sugar_Runtime::OP_NOCACHE:
                $block = $opcodes[++$i];
                if ($cache) {
                    $cache->addBlock($block);
                } else {
                    self::_execute($context, $sugar, $data, $cache, $code, $block, $stack);
                }
                break;
            case Sugar_Runtime::OP_DEREF:
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
            case Sugar_Runtime::OP_MAKE_ARRAY:
                $count = $opcodes[++$i];

                // build array
                $array = array();
                $start = count($stack) - $count * 2;
                for ($index = $start; $index != count($stack); $index += 2) {
                    $array [$stack[$index]]= $stack[$index + 1];
                }

                // remove elements from stack (FIXME: yuck, this is silly)
                while (count($stack) != $start) {
                    array_pop($stack);
                }

                // push resulting array to stack
                $stack []= $array;
                break;
            default:
                throw new Sugar_Exception(
                    'internal error: unknown opcode `'.$opcode.'`'
                );
            }
        }

        return empty($stack) ? null : array_pop($stack);
    }

    /**
     * Display a template (top-level call)
     *
     * @param Sugar_Template $template Template to display
     * @param Sugar_Data     $data     Top-level variable scope
     */
    public function execute(Sugar_Template $template, Sugar_Data $data)
    {
        // stack used for execution
        $stack = array();

        // context used for execution
        $context = new Sugar_Context($this->_sugar, $template, $data, $this);

        try {
            // if we are to be cached, check for an existing cache and use that if
            // it exists and is up to date
            if (!$this->_sugar->debug && !is_null($template->cacheId)) {
                $code = $this->_sugar->getLoader()->getCached($template);
                if ($code !== false) {
                    // add to existing cache, if any
                    if ($this->_cache) {
                        $this->_cache->addRef($template);
                    }

                    // execute cached code
                    self::_execute($context, $this->_sugar, $data, $this->_cache, $code, $code->getSection('main'), $stack);
                    return true;
                }
            }

            // if we are to be cached and aren't alrady running inside an existing
            // cache handler instance, create a new one
            if (!$this->_cache && !is_null($template->cacheId)) {
                /**
                 * Cache handler.
                 */
                include_once $GLOBALS['__sugar_rootdir'].'/Sugar/Cache.php';

                // create cache
                $this->_cache = new Sugar_Cache($this->_sugar);
                $startCache = true;
            } else {
                $startCache = false;
            }

            // add template code to cache's reference
            if ($this->_cache) {
                $this->_cache->addRef($template);
            }

            // load compiled template
            $code = $this->_sugar->getLoader()->getCompiled($template);

            // if we have an inherited template, load it and merge it with our data
            $inherit = $template->getInherit();
            if (!$inherit) {
                $inherit = $code->getInherit();
            }
            if ($inherit) {
                // load compiled parent (inherited template)
                $parent = $this->_sugar->getTemplate($inherit, $template->cacheId);
                if ($parent === false) {
                    throw new Sugar_Exception_Usage('inherited template not found: '.$inherit);
                }
                $pcode = $this->_sugar->getLoader()->getCompiled($parent);

                // merge code
                $pcode->mergeChild($code);
                $code = $pcode;
                unset($pcode);
            }

            // execute our compiled template
            self::_execute($context, $this->_sugar, $data, $this->_cache, $code, $code->getSection('main'), $stack);

            // clean up the cache handler and display the uncachable data if
            // and only if we created the cache handler
            if ($startCache) {
                $code = $this->_cache->getOutput();
                $this->_cache = null;

                // attempt to save cache
                $this->_sugar->cache->store($template, Sugar::CACHE_HTML, $code);

                // save cache in loader
                $this->_sugar->getLoader()->setCached($template, $code);

                // display cache
                self::_execute($context, $this->_sugar, $data, $this->_cache, $code, $code->getSection('main'), $stack);
            }

            return true;
        } catch (Sugar_Exception $e) {
            $this->_sugar->handleError($e);
            return false;
        }
    }
}

// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
