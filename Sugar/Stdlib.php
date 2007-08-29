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

class SugarStdlib {
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
        return SugarUtil::jsValue(SugarUtil::getArg($params, 'value'));
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
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
