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
 * @subpackage Stdlib
 * @author Sean Middleditch <sean@awesomeplay.com>
 * @copyright 2007 AwesomePlay Productions, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

/**
 * Namespace containing the Sugar standard library.
 *
 * @package Sugar
 * @subpackage Stdlib
 */
class SugarStdlib {
    /**#@+
     * Standard library functions.
     *
     * Not sure how to document these sanely in phpdoc, since the
     * interesting things to document are the Sugar parameters, not
     * the PHP call interface.
     */
    public static function _include ($sugar, $params) {
        $sugar->display(SugarUtil::getArg($params, 'tpl', 0));
    }

    public static function _eval ($sugar, $params) {
        $sugar->displayString(SugarUtil::getArg($params, 'source', 0));
    }

    public static function _echo ($sugar, $params) {
        return new SugarEscaped(SugarRuntime::showValue(SugarUtil::getArg($params, 'value', 0)));
    }

    public static function urlEncodeAll ($sugar, $params) {
        $result = '';
        foreach($params as $k=>$v) {
            if ($result)
                $result .= '&';
            $result .= urlencode($k) . '=' . urlencode($v);
        }
        return $result;
    }

    public static function urlEncode ($sugar, $params) {
        return urlencode(SugarUtil::getArg($params, 'value', 0));
    }

    public static function jsValue ($sugar, $params) {
        return SugarUtil::jsValue(SugarUtil::getArg($params, 'value', 0));
    }

    public static function date ($sugar, $params) {
        $format = SugarUtil::getArg($params, 'format', 0, 'r');
        $date = SugarUtil::getArg($params, 'date', 1);
        $stamp = SugarUtil::valueToTime($date);
        return date($format, $stamp);
    }

    public static function format ($sugar, $params) {
        if (isset($params['format'])) {
            $args = array($params['format']);
            unset($params['format']);
            $args = array_merge($args, $params);
            return call_user_func_array('sprintf', $args);
        } else {
            return call_user_func_array('sprintf', $params);
        }
    }

    public static function _default ($sugar, $params) {
        $value = SugarUtil::getArg($params, 'value', 0);
        if ($value)
            return $value;
        else
            return SugarUtil::getArg($params, 'default', 1);
    }

    public static function count ($sugar, $params) {
        return count(SugarUtil::getArg($params, 'array', 0));
    }

    public static function selected ($sugar, $params) {
        if (SugarUtil::getArg($params, 'test', 0))
            return new SugarEscaped(' selected="selected" ');
    }

    public static function checked ($sugar, $params) {
        if (SugarUtil::getArg($params, 'test', 0))
            return new SugarEscaped(' checked="checked" ');
    }

    public static function _switch ($sugar, $params) {
        $value = SugarUtil::getArg($params, 'value', 0);

        if (isset($params[$value]))
            return $params[$value];
        elseif (isset($params['default']))
            return $params['default'];
        else
            return $value;
    }

    public static function truncate ($sugar, $params) {
        $text = SugarUtil::getArg($params, 'text', 0);
        $length = SugarUtil::getArg($params, 'length', 1, 72);
        if (strlen($text) <= $length)
            return $text;
        else
            return preg_replace('/\s+?(\S+)?$/', '...', substr($text, 0, $length + 1));
    }

    public static function escape ($sugar, $params) {
        $value = SugarUtil::getArg($params, 'value', 0);
        $mode = SugarUtil::getArg($params, 'mode', 1, 'html');

        switch ($mode) {
            case 'html':
                return new SugarEscaped(htmlentities($value, ENT_QUOTES, $sugar->charset));
            case 'xml':
                return new SugarEscaped(htmlspecialchars($value, ENT_QUOTES, $sugar->charset));
            case 'js':
                return new SugarEscaped(SugarUtil::jsValue($value));
            case 'url':
                return new SugarEscaped(urlencode($value));
            default:
                return null;
        }
    }

    public static function _var ($sugar, $params) {
        $name = SugarUtil::getArg($params, 'name', 0);
        return $sugar->getVariable($name);
    }

    public static function _array ($sugar, $params) {
        return $params;
    }

    public static function strtolower ($sugar, $params) {
        return strtolower(SugarUtil::getArg($params, 'string', 0));
    }

    public static function strtoupper ($sugar, $params) {
        return strtoupper(SugarUtil::getArg($params, 'string', 0));
    }

    public static function substr ($sugar, $params) {
        $string = SugarUtil::getArg($params, 'string', 0);
        $start = SugarUtil::getArg($params, 'start', 1);
        $length = SugarUtil::getArg($params, 'length', 2);
        return substr($string, $start, $length);
    }

    public static function nl2br ($sugar, $params) {
        $string = SugarUtil::getArg($params, 'string', 0);
        return new SugarEscaped(nl2br($sugar->escape($string)));
    }

    public static function cycle ($sugar, $params) {
        $value = $sugar->getVariable('$sugar.cycle');
        $sugar->set('$sugar.cycle', !$value);
        return (int)$value;
    }

    public static function _isset ($sugar, $params) {
        $obj = SugarUtil::getArg($params, 'object', 0);
        $index = SugarUtil::getArg($params, 'index', 1);
        if (is_array($obj) && isset($obj[$index]))
            return true;
        elseif (is_object($obj) && isset($obj->$index))
            return true;
        else
            return false;
    }
    /**#@-*/

    /**
     * Initialize the standard library.  Called by the Sugar constructor.
     *
     * @param Sugar $sugar Sugar instance.
     */
    public static function initialize ($sugar) {
        $sugar->registerList(array(
            'include' => array(array('SugarStdlib', '_include'), 0),
            'eval' => array(array('SugarStdlib', '_eval'), 0),
            'echo' => array(array('SugarStdlib', '_echo'), 0),
            'raw' => array(array('SugarStdlib', '_echo'), 0),
            'urlencodeall' => array(array('SugarStdlib', 'urlEncodeAll'), 0),
            'urlencode' => array(array('SugarStdlib', 'urlEncode'), 0),
            'jsvalue' => array(array('SugarStdlib', 'jsValue'), 0),
            'default' => array(array('SugarStdlib', '_default'), 0),
            'date' => array(array('SugarStdlib', 'date'), 0),
            'format' => array(array('SugarStdlib', 'format'), 0),
            'count' => array(array('SugarStdlib', 'count'), 0),
            'selected' => array(array('SugarStdlib', 'selected'), 0),
            'checked' => array(array('SugarStdlib', 'checked'), 0),
            'switch' => array(array('SugarStdlib', '_switch'), 0),
            'basename' => array('basename', SUGAR_FUNC_NATIVE),
            'truncate' => array(array('SugarStdlib', 'truncate'), 0),
            'escape' => array(array('SugarStdlib', 'escape'), 0),
            'var' => array(array('SugarStdlib', '_var'), 0),
            'array' => array(array('SugarStdlib', '_array'), 0),
            'strtoupper' => array(array('SugarStdlib', 'strtoupper'), 0),
            'strtolower' => array(array('SugarStdlib', 'strtolower'), 0),
            'substr' => array(array('SugarStdlib', 'substr'), 0),
            'time' => array('time', SUGAR_FUNC_NATIVE),
            'nl2br' => array(array('SugarStdlib', 'nl2br'), 0),
            'cycle' => array(array('SugarStdlib', 'cycle'), 0),
            'isset' => array(array('SugarStdlib', '_isset'), 0),
        ));
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
