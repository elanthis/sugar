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

    public static function jsValue ($value) {
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
                return "'".str_replace("\n", '\\n', addslashes($value))."'";
        }
    }

    function valueToTime ($value) {
        // raw int?  it's a timestamp
        if (is_int($value))
            return $value;
        // otherwise, convert it with strtotime
        elseif (is_string($value))
            return strtotime($value);
        // something... use current time
        else
            return time();
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
