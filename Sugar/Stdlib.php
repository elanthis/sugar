<?php
/**
 * PHP-Sugar Template Engine
 *
 * Copyright (c) 2008  AwesomePlay Productions, Inc. and
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
 * @copyright 2008 AwesomePlay Productions, Inc. and contributors
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

    /*++
     *+ @name include
     *+ @param string $tpl The template path to include.
     *+
     *+ This function loads up a template file and displays it.
     */
    public static function _include ($sugar, $params) {
        $sugar->display(SugarUtil::getArg($params, 'tpl', 0));
    }

    /*++
     *+ @name eval
     *+ @param string $source The template code to evaluate.
     *+ @return string The output of the source after evaluation.
     *+
     *+ Evaluate template code given as a string and reeturn the result.
     */
    public static function _eval ($sugar, $params) {
        $sugar->displayString(SugarUtil::getArg($params, 'source', 0));
    }

    /*++
     *+ @name echo
     *+ @alias raw
     *+ @param string $value The value to display.
     *+ @return raw The input string in raw form.
     *+
     *+ Calling this function results in unescaped output, allowing
     *+ the template author to print variables and strings that contain
     *+ HTML tags without any transformations.
     */
    public static function _echo ($sugar, $params) {
        return new SugarEscaped(SugarRuntime::showValue(SugarUtil::getArg($params, 'value', 0)));
    }

    /*++
     *+ @name urlencode
     *+ @param array|string $value The value to encode, or an array of key/value pairs.
     *+ @return string URL-encoded string.
     *+
     *+ Converts an input string into a URL-encoded string.  If the input
     *+ value is an array, the result is a URL-encoded string of each
     *+ key/value pair separated by ampersands (&).
     */
    public static function urlencode ($sugar, $params) {
        $value = SugarUtil::getArg($params, 'value', 0);
        if (is_array($value)) {
            $result = array();
            foreach($value as $k=>$v)
                $result []= urlencode($k) . '=' . urlencode($v);
            return implode('&', $result);
        } else {
            return urlencode($value);
        }
    }

    /*++
     *+ @name jsvalue
     *+ @param mixed $value Value to encode.
     *+ @return string Value encoded in JavaScript notation.
     *+
     *+ Convers the input value into the proper code necessary to
     *+ recreate the value in JavaScript notation.  Useful for
     *+ exporting template variables to JavaScript.
     */
    public static function jsvalue ($sugar, $params) {
        return SugarUtil::jsvalue(SugarUtil::getArg($params, 'value', 0));
    }

    /*++
     *+ @name date
     *+ @param string $format The format to use, from the PHP date() function. (default 'r')
     *+ @param mixed? $date The current date, either as a string or a timestamp.
     *+ @return string The formatted date.
     *+
     *+ Formats the input date, or the current date if no date is given.
     */
    public static function date ($sugar, $params) {
        $format = SugarUtil::getArg($params, 'format', 0, 'r');
        $date = SugarUtil::getArg($params, 'date', 1);
        $stamp = SugarUtil::valueToTime($date);
        return date($format, $stamp);
    }

    /*++
     *+ @name printf
     *+ @alias format
     *+ @param string $format Format string.
     *+ @varargs mixed
     *+ @return string Formatted string.
     *+
     *+ Formats the input arguments using sprintf().
     */
    public static function format ($sugar, $params) {
        if (isset($params['format'])) {
            $format = $params['format'];
            unset($params['format']);
        } else {
            $format = array_shift($params);
        }
        return vsprintf($format, $params);
    }

    /*++
     *+ @name default
     *+ @param mixed $value The value to test and return if true.
     *+ @param mixed $default The value to return if $value is false.
     *+ @return mixed $value if it is true, otherwise $false.
     *+
     *+ Tests the first value given and, if it is a true value, returns
     *+ that value.  If the value is false, the second value is returned
     *+ instead.
     *+
     *+ The code
     *+   <% default $value, $default %>
     *+ is equivalent to
     *+   <% if $value ; $value ; else ; $default ; end %>
     *+
     *+ This is particularly useful for the value attribute for form
     *+ input tags when used in conjunction with a user-input value
     *+ and the form's default value.
     */
    public static function _default ($sugar, $params) {
        $value = SugarUtil::getArg($params, 'value', 0);
        if ($value)
            return $value;
        else
            return SugarUtil::getArg($params, 'default', 1);
    }

    /*++
     *+ @name count 
     *+ @param array $array Array to count.
     *+ @return int Number of elements in the array.
     *+
     *+ Returns the number of elements within the given array,
     *+ using the internal PHP count() function.
     */
    public static function count ($sugar, $params) {
        return count(SugarUtil::getArg($params, 'array', 0));
    }

    /*++
     *+ @name selected
     *+ @param mixed $test The test expression.
     *+ @return string The string ' selected="selected" ' if $test is true.
     *+
     *+ If the given input is a true value, then the HTML attribute code
     *+ selected="selected" is returned.
     *+
     *+ This is useful to use inside of HTML option tags to determine if
     *+ the option should be selected by default.  e.g.
     *+   <option <% selected $value=='First' %>>First</option>
     *+   <option <% selected $value=='Second' %>>Second</option>
     *+   <option <% selected $value=='Third' %>>Third</option>
     */
    public static function selected ($sugar, $params) {
        if (SugarUtil::getArg($params, 'test', 0))
            return new SugarEscaped(' selected="selected" ');
    }

    /*++
     *+ @name checked
     *+ @param mixed $test The test expression.
     *+ @return string The string ' checked="checked" ' if $test is true.
     *+
     *+ If the given input is a true value, then the HTML attribute code
     *+ checked="checked" is returned.
     *+
     *+ This is useful to use inside of HTML checkbox and radio input tags
     *+ to determine if the element should be checked by default.  e.g.
     *+   <input type="checkbox" name="first" <% checked $first=='on' %>>
     *+   <input type="checkbox" name="second" <% checked $second=='on' %>>
     */
    public static function checked ($sugar, $params) {
        if (SugarUtil::getArg($params, 'test', 0))
            return new SugarEscaped(' checked="checked" ');
    }

    /*++
     *+ @name switch
     *+ @param mixed $value The value to look for.
     *+ @param mixed $default The value to return if $value is not found.
     *+ @varargs mixed
     *+
     *+ Given a list of named parameters, return the parameter with the name
     *+ equal to the $value parameter.  If no such parameter is found, return
     *+ the $default parameter, or the value of $value itself if $default is
     *+ not set.
     *+
     *+ Example:
     *+   $n = 'foo'; switch $n, 'not found', foo='Found Foo', bar='Found Bar'
     *+   // would print 'Found Foo'
     */
    public static function _switch ($sugar, $params) {
        $value = SugarUtil::getArg($params, 'value', 0);
        $default = SugarUtil::getArg($params, 'default', 1, $value);

        if (isset($params[$value]))
            return $params[$value];
        elseif (isset($params['default']))
            return $params['default'];
        else
            return $value;
    }

    /*++
     *+ @name truncate
     *+ @param string $string The string to truncade.
     *+ @param int $length The maximum length.  (default 72)
     *+ @param string $end String to append after truncation. (default '...')
     *+ @return string The truncated string
     *+
     *+ Truncates the input string to a maximum of $length characters.  If
     *+ the string requires truncation, the value of $end will be appended
     *+ to the truncated string.  The length of $end is taken into account
     *+ to ensure that the result will never be more than $length characters.
     */
    public static function truncate ($sugar, $params) {
        $text = SugarUtil::getArg($params, 'string', 0);
        $length = SugarUtil::getArg($params, 'length', 1, 72);
        $end = SugarUtil::getArg($params, 'end', 2, '...');
        if (strlen($text) <= $length)
            return $text;
        else
            return preg_replace('/\s+?(\S+)?$/', $end, substr($text, 0, $length - strlen($end) + 1));
    }

    /*++
     *+ @name escape
     *+ @param mixed $value Value to escape.
     *+ @param string $mode Escape format to use.  (default 'html')
     *+ @return raw Escaped value.
     *+
     *+ Mode must be one of 'html', 'xml', 'js', or 'url'.
     *+
     *+ The input value is escaped according to the rules of $mode, resulting
     *+ in a raw string which can be safely printed out.
     *+
     *+ For the mode 'js', this is equivalent to:
     *+   <% echo jsvalue $value %>
     *+
     *+ For the mode 'url', this is equivalent to:
     *+   <% echo urlencode $value %>
     *+
     *+ For the modes 'html' and 'xml', this is equivalent to the default
     *+ output encoding rules for both languages.
     */
    public static function escape ($sugar, $params) {
        $value = SugarUtil::getArg($params, 'value', 0);
        $mode = SugarUtil::getArg($params, 'mode', 1, 'html');

        switch ($mode) {
            case 'html':
                return new SugarEscaped(htmlentities($value, ENT_QUOTES, $sugar->charset));
            case 'xml':
                return new SugarEscaped(htmlspecialchars($value, ENT_QUOTES, $sugar->charset));
            case 'js':
                return new SugarEscaped(SugarUtil::jsvalue($value));
            case 'url':
                return new SugarEscaped(urlencode($value));
            default:
                return null;
        }
    }

    /*++
     *+ @name var
     *+ @param string $name The variable to lookup.
     *+ @return mixed The value of the requested variable.
     *+
     *+ This returns the value of the requested variable.  This function is
     *+ useful when the name of a variable required is known only by the value
     *+ of another variable.
     *+
     *+ In particular, these three lines are equivalent:
     *+   <% $foo %>
     *+   <% var 'foo' %>
     *+   <% $name = 'foo' ; var $name %>
     */
    public static function _var ($sugar, $params) {
        $name = SugarUtil::getArg($params, 'name', 0);
        return $sugar->getVariable($name);
    }

    /*++
     *+ @name array
     *+ @varargs mixed
     *+ @return array All of the parameters returned as an array.
     *+
     *+ Returns all of the parameters converted into an array.  Named
     *+ parameters result in appropriate array keys, while unnamed
     *+ parameters result in appropriate array indexes.
     */
    public static function _array ($sugar, $params) {
        return $params;
    }

    /*++
     *+ @name strtolower
     *+ @param string $string The string to process.
     *+ @return string $string with all characters lower-cased.
     *+
     *+ Equivalent to PHP's strtolower().
     */
    public static function strtolower ($sugar, $params) {
        return strtolower(SugarUtil::getArg($params, 'string', 0));
    }

    /*++
     *+ @name strtoupper
     *+ @param string $string The string to process.
     *+ @return string $string with all characters upper-cased.
     *+
     *+ Equivalent to PHP's strtoupper().
     */
    public static function strtoupper ($sugar, $params) {
        return strtoupper(SugarUtil::getArg($params, 'string', 0));
    }

    /*++
     *+ @name substr
     *+ @param string $string The string to cut.
     *+ @param int $start The position to cut at.
     *+ @param int $length The number of characters to cut.
     *+ @return string The cut string.
     *+
     *+ Returns a portion of the input string, no more than $length characters
     *+ long, starting the character index $index.
     *+
     *+ Examples:
     *+   substr 'hello world', 2, 6 // llo wo
     *+   substr 'hello world', 0, 5 // hello
     *+   substr 'hello world', 6, 5 // world
     *+   substr 'hello world', 10, 5 // ld
     */
    public static function substr ($sugar, $params) {
        $string = SugarUtil::getArg($params, 'string', 0);
        $start = SugarUtil::getArg($params, 'start', 1);
        $length = SugarUtil::getArg($params, 'length', 2);
        return substr($string, $start, $length);
    }

    /*++
     *+ @name nl2br
     *+ @param string $string The string to process.
     *+ @return raw $string all newlines replaced with '<br />' and all HTML special characters escaped.
     *+
     *+ Equivalent to calling Sugar::escape() followed by PHP's nl2br().
     */
    public static function nl2br ($sugar, $params) {
        $string = SugarUtil::getArg($params, 'string', 0);
        return new SugarEscaped(nl2br($sugar->escape($string)));
    }

    /*++
     *+ @name cycle
     *+ @return int Alternates between returning 0 and 1.
     *+
     *+ Returns either 0 or 1, each time returning the opposite of the value
     *+ returned from the prior call.  The first call will return 0, the
     *+ second returns 1, and third returns 0, and so on.
     *+
     *+ This is most useful when printing rows of data that should be
     *+ displayed in alternating colors use CSS.  Example:
     *+  <tr class="row<% cycle %>">
     */
    public static function cycle ($sugar, $params) {
        $value = $sugar->getVariable('$sugar.cycle');
        $sugar->set('$sugar.cycle', !$value);
        return (int)$value;
    }

    /*++
     *+ @name isset
     *+ @param array|object $object Array or object to look inside.
     *+ @param mixed $index The index to look for.
     *+ @return bool True if the index is found, false otherwise.
     *+
     *+ Equivalent to PHP's isset() function.
     */
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

    /*++
     *+ @name time
     *+ @return int Current UNIX timestamp.
     *+
     *+ Equivalent to PHP's time().
     */
    public static function time ($sugar, $params) {
        return time();
    }

    /*++
     *+ @name basename
     *+ @param string $path File path name.
     *+ @return string Just the file portion of $path.
     *+
     *+ Equivalent to PHP's basename().
     */
    public static function basename ($sugar, $params) {
        return basename(SugarUtil::getArg($params, 'path', 0));
    }

		/*++
		 *+ @name merge
		 *+ @varargs array
		 *+ @return array All input arrays merged.
		 *+
		 *+ Takes any number of arrays and merges them into a single array.
		 *+ Non-array values given are ignored.
		 *+
		 *+ Equivalent to PHP's array_merge().
		 */
		public static function merge ($sugar, $params) {
			// clean params of non-arrays
			foreach ($params as $i=>$k) {
				if (!is_array($k))
					unset($params[$i]);
			}
			// use array_merge
			return call_user_func_array('array_merge', $params);
		}

		/*++
		 *+ @name join
		 *+ @alias implode
		 *+ @param string $separator String to put between joined elements.
		 *+ @param array $array Array to join.
		 *+ @return string All elements of $array joined together.
		 *+
		 *+ Joins all of the elements of the given array together into a
		 *+ single string, with each element separated by the given
		 *+ separator.
		 *+
		 *+ Equivalent to PHP's implode().
		 */
		public static function join ($sugar, $params) {
			$sep = (string)SugarUtil::getArg($params, 'separator', 0, ' ');
			$array = (array)SugarUtil::getArg($params, 'array', 1);
			return implode($sep, $array);
		}

		/*++
		 *+ @name split
		 *+ @alias explode
		 *+ @param string $delimiter Separator to split on.
		 *+ @param string $string String to split.
		 *+ @param int $count Maximum elements in result, or infinite if unset.
		 *+
		 *+ Splits the given input string into an array of elements.
		 *+
		 *+ Equivalent to PHP's explode().
		 */
		public static function split ($sugar, $params) {
			$sep = (string)SugarUtil::getArg($params, 'delimiter', 0, ' ');
			$string = (string)SugarUtil::getArg($params, 'string', 1);
			$count = SugarUtil::getArg($params, 'count', 2);
			if (!is_null($count))
				return explode($sep, $string, $count);
			else
				return explode($sep, $string);
		}

		/*++
		 *+ @name psplit
		 *+ @param string $expr Regular expression to split on.
		 *+ @param string $string String to split.
		 *+ @param int $count Maximum elements in result, or infinite if unset.
		 *+
		 *+ Splits the given input string into an array of elements using
		 *+ a regular expression as the delimiter.
		 *+
		 *+ Example:
		 *+   <% psplit '/\s+/', $string %>
		 *+
		 *+ Equivalent to PHP's preg_split().
		 */
		public static function psplit ($sugar, $params) {
			$expr = (string)SugarUtil::getArg($params, 'expr', 0, ' ');
			$string = (string)SugarUtil::getArg($params, 'string', 1);
			$count = SugarUtil::getArg($params, 'count', 2);
			return preg_split($expr, $string, $count);
		}

    /**#@-*/

    /**
     * Initialize the standard library.  Called by the Sugar constructor.
     *
     * @param Sugar $sugar Sugar instance.
     */
    public static function initialize ($sugar) {
        $sugar->registerList(array(
            'include' => array('SugarStdlib', '_include'),
            'eval' => array('SugarStdlib', '_eval'),
            'echo' => array('SugarStdlib', '_echo'),
            'raw' => array('SugarStdlib', '_echo'),
            'urlencode' => array('SugarStdlib', 'urlencode'),
            'jsvalue' => array('SugarStdlib', 'jsvalue'),
            'default' => array('SugarStdlib', '_default'),
            'date' => array('SugarStdlib', 'date'),
            'format' => array('SugarStdlib', 'format'),
            'printf' => array('SugarStdlib', 'format'),
            'count' => array('SugarStdlib', 'count'),
            'selected' => array('SugarStdlib', 'selected'),
            'checked' => array('SugarStdlib', 'checked'),
            'switch' => array('SugarStdlib', '_switch'),
            'truncate' => array('SugarStdlib', 'truncate'),
            'escape' => array('SugarStdlib', 'escape'),
            'var' => array('SugarStdlib', '_var'),
            'array' => array('SugarStdlib', '_array'),
            'strtoupper' => array('SugarStdlib', 'strtoupper'),
            'strtolower' => array('SugarStdlib', 'strtolower'),
            'substr' => array('SugarStdlib', 'substr'),
            'nl2br' => array('SugarStdlib', 'nl2br'),
            'cycle' => array('SugarStdlib', 'cycle'),
            'isset' => array('SugarStdlib', '_isset'),
            'time' => array('SugarStdlib', 'time'),
            'basename' => array('SugarStdlib', 'basename'),
            'merge' => array('SugarStdlib', 'merge'),
            'join' => array('SugarStdlib', 'join'),
            'implode' => array('SugarStdlib', 'join'),
            'split' => array('SugarStdlib', 'split'),
            'explode' => array('SugarStdlib', 'split'),
            'psplit' => array('SugarStdlib', 'psplit'),
        ));
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4: ?>
