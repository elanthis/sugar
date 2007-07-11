<?php
class SugarStdlib {
    public static function _include (&$sugar, $params) {
        $sugar->display(SugarUtil::getArg($params, 'tpl', 0));
    }

    public static function _eval (&$sugar, $params) {
        $sugar->displayString(SugarUtil::getArg($params, 'source'));
    }

    public static function _echo (&$sugar, $params) {
        echo SugarRuntime::showValue(SugarUtil::getArg($params, 'val'));
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

    public static function initialize (&$sugar) {
        $sugar->register('include', array('SugarStdlib', '_include'));
        $sugar->register('eval', array('SugarStdlib', '_eval'));
        $sugar->register('echo', array('SugarStdlib', '_echo'));
        $sugar->register('urlEncodeAll', array('SugarStdlib', 'urlEncodeAll'));
        $sugar->register('urlEncode', array('SugarStdlib', 'urlEncode'));
        $sugar->register('jsValue', array('SugarStdlib', 'jsValue'));
        $sugar->register('date', array('SugarStdlib', 'date'));
        $sugar->register('format', array('SugarStdlib', 'format'));
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
