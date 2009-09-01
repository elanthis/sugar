<?php
/**
 * Miscellaneous utility functions used by Sugar.
 *
 * Provides several utility functions used by the Sugar codebase.
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
 * @category  Template
 * @package   Sugar
 * @author    Sean Middleditch <sean@mojodo.com>
 * @copyright 2008-2009 Mojodo, Inc. and contributors
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @version   SVN: $Id$
 * @link      http://php-sugar.net
 */

/**
 * Utility functions for Sugar.
 *
 * Namespace for utility functions useful in Sugar functions.
 *
 * @category  Template
 * @package   Sugar
 * @author    Sean Middleditch <sean@mojodo.com>
 * @copyright 2008-2009 Mojodo, Inc. and contributors
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @version   Release: 0.82
 * @link      http://php-sugar.net
 */
class SugarUtil
{
    /**
     * Returns an argument from a function parameter list, supporting both
     * position and named parameters and default values.
     *
     * @param array  $params  Function parameter list.
     * @param string $name    Parameter name.
     * @param mixed  $default Default value if parameter is not specified.
     *
     * @return mixed Value of parameter if given, or the default value otherwise.
     */
    public static function getArg($params, $name, $default = null)
    {
        return isset($params[$name]) ? $params[$name] : $default;
    }

    /**
     * Checks if an array is a "vector," or an array with only integral
     * indexes starting at zero and incrementally increasing.  Used only
     * for nice exporting to JavaScript.
     *
     * Only really used for {@link SugarUtil::json}.
     *
     * @param array $array Array to check.
     *
     * @return bool True if array is a vector.
     */
    public static function isVector($array)
    {
        if (!is_array($array)) {
            return false;
        }

        $next = 0;
        foreach ($array as $k=>$v) {
            if ($k !== $next) {
                return false;
            }
            ++$next;
        }
        return true;
    }

    /**
     * Formats a PHP value in JavaScript format.
     *
     * We can probably juse use json_encode() instead of this, except
     * json_encode() is PHP 5.2 only.
     *
     * @param mixed $value Value to format.
     *
     * @return string Formatted result.
     */
    public static function json($value)
    {
        switch (gettype($value)) {
        case 'integer':
        case 'float':
            return $value;
        case 'array':
            if (SugarUtil::isVector($value)) {
                $escaped = array_map(array('SugarUtil', 'json'), $value);
                return '['.implode(',', $escaped).']';
            }

            $result = '{';
            $first = true;
            foreach ($value as $k=>$v) {
                if (!$first) {
                    $result .= ',';
                } else {
                    $first = false;
                }
                $result .= SugarUtil::json($k).':'.SugarUtil::json($v);
            }
            $result .= '}';
            return $result;
        case 'object':
            $result = '{\'phpType\':'.SugarUtil::json(get_class($value));
            foreach (get_object_vars($value) as $k=>$v) {
                $result .= ',' . SugarUtil::json($k).':'.SugarUtil::json($v);
            }
            $result .= '}';
            return $result;
        case 'null':
            return 'null';
        default:
            $escaped = addslashes($value);
            $escaped = str_replace(array("\n", "\r", "\r\b"), '\\n', $escaped);
            return "'".$escaped."'";
        }
    }

    /**
     * Convert a value into a timestamp.  This is essentially strtotime(),
     * except that if an integer timestamp is passed in it is returned
     * verbatim, and if the value cannot be parsed, it returns the current
     * timestamp.
     *
     * @param mixed $value Time value to parse.
     *
     * @return int Timestamp.
     */
    public static function valueToTime($value)
    {
        if (is_int($value)) {
            // raw int?  it's a timestamp
            return $value;
        } elseif (is_string($value)) {
            // otherwise, convert it with strtotime
            return strtotime($value);
        } else {
            // something... use current time
            return time();
        }
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
