<?php
class SugarStdlib {
    public static function _include (&$sugar, $params) {
        $sugar->display(SugarUtil::getArg($params, 'tpl', 0));
    }

    public static function _eval (&$sugar, $params) {
        $sugar->displayString(SugarUtil::getArg($params, 'source'));
    }

    public static function _echo (&$sugar, $params) {
        return new SugarEscaped(SugarRuntime::showValue(SugarUtil::getArg($params, 'val')));
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
        return urlencode(SugarUtil::getArg($params, 'val'));
    }

    public static function jsValue ($sugar, $params) {
        return SugarUtil::jsValue(SugarUtil::getArg($params, 'val'));
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
                return new SugarEscaped(htmlentities($value));
            case 'xml':
                return new SugarEscaped(SugarUtil::xmlentities($value));
            case 'js':
                return new SugarEscaped(SugarUtil::jsValue($value));
            case 'url':
                return new SugarEscaped(urlencode($value));
            default:
                return null;
        }
    }

    public static function initialize (&$sugar) {
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
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
