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
 * Formats a PHP value in JavaScript format.
 *
 * We can probably juse use json_encode() instead of this, except
 * json_encode() is PHP 5.2 only.
 *
 * @param mixed $value Value to format.
 *
 * @return string Formatted result.
 */
function Sugar_Util_Json($value)
{
    // use json_encode, if defined
    if (function_exists('json_encode')) {
        return json_encode($value);
    }

    switch (gettype($value)) {
    case 'integer':
    case 'float':
        return $value;
    case 'array':
        // check if our value is a vector (array with increasing numerical indices)
        if (is_array($array)) {
            $next = 0;
            $isVector = true;
            foreach ($array as $k=>$v) {
                if ($k !== $next) {
                    $isVector = false;
                    break;
                }
                ++$next;
            }

            // if we have a vector, use an array encoding
            if ($isVector) {
                $escaped = array_map('Sugar_Util_Json', $value);
                return '['.implode(',', $escaped).']';
            }
        }

        // we do not have a vector, so process as an object
        $result = '{';
        $first = true;
        foreach ($value as $k=>$v) {
            if (!$first) {
                $result .= ',';
            } else {
                $first = false;
            }
            $result .= Sugar_Util_Json($k).':'.Sugar_Util_Json($v);
        }
        $result .= '}';
        return $result;
    case 'object':
        $result = '{\'phpType\':'.Sugar_Util_Json(get_class($value));
        foreach (get_object_vars($value) as $k=>$v) {
            $result .= ',' . Sugar_Util_Json($k).':'.Sugar_Util_Json($v);
        }
        $result .= '}';
        return $result;
    case 'null':
        return 'null';
    default:
        $escaped = str_replace(array("\n", "\r", "\r\n"), '\\n', addslashes($value));
        return '"'.$escaped.'"';
    }
}

// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
