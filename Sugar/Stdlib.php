<?php
class SugarUtil {
    public static function isVector (&$array) {
        if (!is_array($array))
            return false;
        $next = 0;
        foreach ($array as $k=>$v) {
            if ($k !== $next)
                return false;
        }
        return true;
    }

    public static function jsValue (&$value) {
        switch (gettype($value)) {
            case 'integer':
            case 'float':
                return $value;
            case 'array':
                if (SugarUtil::isVector($array))
                    return '['.implode(',', array('SugarUtil', 'jsValue')).']';

                $result = '{';
                $first = true;
                foreach($value as $k=>$v) {
                    if (!$first)
                        $result .= ',';
                    else
                        $first = false;
                    $result .= SugarUtil::jsValue($k).':'.SugarUtil::jsValue($v);
                }
                $result .= '}';
                return $result;
            case 'object':
                $result = '{\'phpType\':'.SugarUtil::jsValue(get_class($value));
                foreach(get_object_vars($value) as $k=>$v)
                    $result .= ',' . SugarUtil::jsValue($k).':'.SugarUtil::jsValue($v);
                $result .= '}';
                return $result;
            case 'null':
                return 'null';
            default:
                return "'".addslashes($value)."'";
        }
    }
}

class SugarStdlib {
    public static function _include (&$sugar, $params) {
        $sugar->display($params[0]);
    }

    public static function _eval (&$sugar, $params) {
        $sugar->displayString($params[0]);
    }

    public static function _echo (&$sugar, $params) {
        echo SugarRuntime::showValue($params[0]);
    }

    public static function encodeUrl ($data='') {
        if (is_array($params[0]))
            return implode('&', array_map('urlencode', $params[0]));
        else
            return urlencode($params[0]);
    }

    public static function jsValue ($value=null) {
        return SugarUtil::jsValue($value);
    }

    public static function initialize (&$sugar) {
        $sugar->register('include', array('SugarStdlib', '_include'));
        $sugar->register('eval', array('SugarStdlib', '_eval'));
        $sugar->register('echo', array('SugarStdlib', '_echo'));
        $sugar->register('urlEncode', array('SugarStdlib', 'encodeUrl'));
        $sugar->register('jsValue', array('SugarStdlib', 'jsValue'), SUGAR_FUNC_NATIVE);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
