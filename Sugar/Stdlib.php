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
    public static function _include (&$sugar, $params) {
        $sugar->display(SugarUtil::getArg($params, 'tpl', 0));
    }

    public static function _eval (&$sugar, $params) {
        $sugar->displayString(SugarUtil::getArg($params, 'source'));
    }

    public static function _echo (&$sugar, $params) {
        return new SugarEscaped(SugarRuntime::showValue(SugarUtil::getArg($params, 'value')));
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
        return urlencode(SugarUtil::getArg($params, 'value'));
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
    /**#@-*/

    /**
     * Initialize the standard library.  Called by the Sugar constructor.
     *
     * @param Sugar $sugar Sugar instance.
     */
    public static function initialize ($sugar) {
        $sugar->register('include', array('SugarStdlib', '_include'));
        $sugar->register('eval', array('SugarStdlib', '_eval'));
        $sugar->register('echo', array('SugarStdlib', '_echo'));
        $sugar->register('raw', array('SugarStdlib', '_echo'));
        $sugar->register('urlEncodeAll', array('SugarStdlib', 'urlEncodeAll'));
        $sugar->register('urlEncode', array('SugarStdlib', 'urlEncode'));
        $sugar->register('jsValue', array('SugarStdlib', 'jsValue'));
        $sugar->register('default', array('SugarStdlib', '_default'));
        $sugar->register('date', array('SugarStdlib', 'date'));
        $sugar->register('format', array('SugarStdlib', 'format'));
        $sugar->register('count', array('SugarStdlib', 'count'));
        $sugar->register('selected', array('SugarStdlib', 'selected'));
        $sugar->register('checked', array('SugarStdlib', 'checked'));
        $sugar->register('switch', array('SugarStdlib', '_switch'));
        $sugar->register('basename', 'basename', SUGAR_FUNC_NATIVE);
        $sugar->register('truncate', array('SugarStdlib', 'truncate'));
        $sugar->register('escape', array('SugarStdlib', 'escape'));
        $sugar->register('var', array('SugarStdlib', '_var'));
        $sugar->register('array', array('SugarStdlib', '_array'));
        $sugar->register('strtoupper', array('SugarStdlib', 'strtoupper'));
        $sugar->register('strtolower', array('SugarStdlib', 'strtolower'));
        $sugar->register('substr', array('SugarStdlib', 'substr'));
        $sugar->register('time', 'time', SUGAR_FUNC_NATIVE);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
