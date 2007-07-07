<?php
class SugarUtil {
    public static function getArg (&$params, $name, $index = 0, $default = null) {
        if (isset($params[$name]))
            return $params[$name];
        elseif (isset($params[$index]))
            return $params[$index];
        else
            return $default;
    }

    public static function isVector (&$array) {
        if (!is_array($array))
            return false;
        $next = 0;
        foreach ($array as $k=>$v) {
            if ($k !== $next)
                return false;
            ++$next;
        }
        return true;
    }

    public static function jsValue (&$value) {
        switch (gettype($value)) {
            case 'integer':
            case 'float':
                return $value;
            case 'array':
                if (SugarUtil::isVector($value))
                    return '['.implode(',', array_map(array('SugarUtil', 'jsValue'), $value)).']';

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

    function valueToTime ($value) {
        if (is_int($value))
            return $value;
        elseif (is_string($value))
            return strtotime($value);
        else
            return time();
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
