<?php
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
        if (is_string($left))
            return $left.$right;
        elseif (is_array($left))
            return array_merge($left, is_array($right)?$right:array($right));
        else
            return $left+$right;
    }

    private static function cacheOutput (&$cache, $text) {
        // if bytecode ends in an echo, append it
        if ($cache[count($cache)-2] == 'echo') {
            $cache[count($cache)-1] .= $text;
        // append a new echo
        } else {
            $cache []= 'echo';
            $cache []= $text;
        }
    }

    public static function execute (&$sugar, $code, &$cache=false) {
        $stack = array();

        for ($i = 0; $i < count($code); ++$i) {
            switch($code[$i]) {
                case 'echo':
                    if ($cache !== false)
                        SugarRuntime::cacheOutput($cache, $code[++$i]);
                    else
                        echo $code[++$i];
                    break;
                case 'print':
                    $val = array_pop($stack);
                    if ($cache !== false)
                        SugarRuntime::cacheOutput($cache, htmlentities(SugarRuntime::showValue($val)));
                    else
                        echo htmlentities(SugarRuntime::showValue($val));
                    break;
                case 'push':
                    $str = $code[++$i];
                    $stack []= $str;
                    break;
                case 'lookup':
                    $var = strtolower($code[++$i]);
                    $stack []= $sugar->get($var);
                    break;
                case 'assign':
                    $name = $code[++$i];
                    $value = array_pop($stack);
                    $sugar->set($name, $value);
                    break;
                case 'negate':
                    $v = array_pop($stack);
                    $stack []= -intval($v);
                    break;
                case '!':
                    $v = array_pop($stack);
                    $stack []= !$v;
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
                    $stack []= intval($v1 / $v2);
                    break;
                case '%':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    $stack []= intval($v1 % $v2);
                    break;
                case '==':
                    $v2 = array_pop($stack);
                    $v1 = array_pop($stack);
                    $stack []= ($v1 == $v2);
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
                    $stack []= ($v1 >= $v2);
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
                        throw new SugarRuntimeException ('unknown function: '.$func);

                    // if we're caching and this is a no-cache function,
                    // append these opcodes to the cache
                    if ($cache !== false && ($invoke[1] & SUGAR_FUNC_NO_CACHE)) {
                        $cache []= 'call';
                        $cache []= $func;
                        $cache []= $args;
                        break;
                    }

                    // compile args
                    $params = array();
                    foreach($args as $name=>$pcode)
                        $params[$name] = SugarRuntime::execute($sugar, $pcode, $cache);

                    // exception net
                    try {
                        // call function, using appropriate method
                        if ($invoke[1] & SUGAR_FUNC_SIMPLE)
                            $ret = call_user_func_array($invoke[0], $params);
                        else
                            $ret = call_user_func($invoke[0], $sugar, $params);

                        // suppress return value if flag is set
                        if ($invoke[1] & SUGAR_FUNC_SUPPRESS_RETURN)
                            $ret = null;

                        // store return value
                        $stack []= $ret;
                    } catch (Exception $e) {
                        throw new SugarRuntimeException ('caught exception: '.$e->getMessage());
                    }
                    break;
                case 'method':
                    $obj = array_pop($stack);
                    $func = $code[++$i];
                    $args = $code[++$i];

                    if (!$sugar->methods)
                        throw new SugarRuntimeException ('method invocation is disabled');

                    if (!is_object($obj))
                        throw new SugarRuntimeException ('method call on non-object');

                    if (!method_exists($obj, $func))
                        throw new SugarRuntimeException ('unknown method on object: '.$func);

                    // compile args
                    $params = array();
                    foreach($args as $pcode)
                        $params [] = SugarRuntime::execute($sugar, $pcode, $cache);

                    // exception net
                    try {
                        $stack []= call_user_func_array(array($obj, $func), $params);
                    } catch (Exception $e) {
                        throw new SugarRuntimeException ('caught exception: '.$e->getMessage());
                    }
                    break;
                case 'if':
                    $test = array_pop($stack);
                    $true = $code[++$i];
                    $false = $code[++$i];
                    if ($test && $true)
                        SugarRuntime::execute($sugar, $true, $cache);
                    elseif (!$test && $false)
                        SugarRuntime::execute($sugar, $false, $cache);
                    break;
                case 'foreach':
                    $array = array_pop($stack);
                    $key = $code[++$i];
                    $name = $code[++$i];
                    $block = $code[++$i];
                    foreach($array as $k=>$v) {
                        if ($key)
                            $sugar->set($key, $k);
                        $sugar->set($name, $v);
                        SugarRuntime::execute($sugar, $block, $cache);
                    }
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
                default:
                    throw new SugarRuntimeException ('unknown opcode: '.$code[$i]);
            }
        }

        return $stack[0];
    }

    public static function run (&$sugar, $data) {
        SugarRuntime::execute($sugar, $data);
    }

    public static function makeCache (&$sugar, $data) {
        // build cache
        $cache = array();
        SugarRuntime::execute($sugar, $data, $cache);
        return $cache;
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
