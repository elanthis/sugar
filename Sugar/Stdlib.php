<?php
/**
 * Sugar template function standard library.
 *
 * These are all of the built-in standard template functions that ship with
 * Sugar.  Note that the functions are not documented in phpdoc, as the
 * functions are of little interest to PHP developers; the important
 * information is related to how they are called from Sugar, and a custom
 * documentation parser has been written for generating that documentation.
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
 * @category Template
 * @package Sugar
 * @subpackage Stdlib
 * @author Sean Middleditch <sean@mojodo.com>
 * @copyright 2008 Mojodo, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 * @version 0.81
 * @link http://php-sugar.net
 */

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
function sugar_function_include($sugar, $params)
{
    $tpl = SugarUtil::getArg($params, 'tpl');
    unset($params['tpl']);

    $sugar->display($tpl, $params);
}

/*++
 *+ @name eval
 *+ @param string $source The template code to evaluate.
 *+ @return string The output of the source after evaluation.
 *+
 *+ Evaluate template code given as a string and reeturn the result.
 */
function sugar_function_eval($sugar, $params)
{
    $source = SugarUtil::getArg($params, 'source');
    unset($params['source']);

    $sugar->displayString($source, $params);
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
function sugar_function_echo($sugar, $params)
{
    $value = SugarUtil::getArg($params, 'value');
    return new SugarEscaped(SugarRuntime::showvalue($value));
}
function sugar_function_raw($s, $p)
{
    return sugar_function_echo($s, $p);
}

/*++
 *+ @name urlencode
 *+ @param string? $string A string to encode.
 *+ @param array? $array An array of key/value pairs.
 *+ @return string URL-encoded string.
 *+
 *+ Converts an input string into a URL-encoded string.  If the input
 *+ value is an array, the result is a URL-encoded string of each
 *+ key/value pair separated by ampersands (&).
 */
function sugar_function_urlencode($sugar, $params)
{
    $string = (string)SugarUtil::getArg($params, 'string');
    $array = SugarUtil::getArg($params, 'array');
    if (is_array($array)) {
        $result = array();
        foreach($array as $k=>$v)
            $result []= urlencode($k) . '=' . urlencode($v);
        return implode('&', $result);
    } else {
        return urlencode($string);
    }
}

/*++
 *+ @name json
 *+ @param mixed $value Value to encode.
 *+ @return string Value encoded in JSON/JavaScript notation.
 *+
 *+ Convers the input value into the proper code necessary to
 *+ recreate the value in JSON notation.  Useful for
 *+ exporting template variables to JavaScript.
 */
function sugar_function_json($sugar, $params)
{
    return SugarUtil::json(SugarUtil::getArg($params, 'value'));
}

/*++
 *+ @name date
 *+ @param string $format The format to use, from the PHP date() function. (default 'r')
 *+ @param mixed? $date The current date, either as a string or a timestamp.
 *+ @return string The formatted date.
 *+
 *+ Formats the input date, or the current date if no date is given.
 */
function sugar_function_date($sugar, $params)
{
    $format = SugarUtil::getArg($params, 'format', 'r');
    $date = SugarUtil::getArg($params, 'date');
    $stamp = SugarUtil::valueToTime($date);
    return date($format, $stamp);
}

/*++
 *+ @name printf
 *+ @alias sprintf
 *+ @param string $format Format string.
 *+ @param array $params Format parameters.
 *+ @return string Formatted string.
 *+
 *+ Formats the input arguments using sprintf().
 */
function sugar_function_printf($sugar, $params)
{
    $format = (string)SugarUtil::getArg($params, 'format');
    $args = SugarUtil::getArg($params, 'params');
    if (is_array($args))
        return vsprintf($format, $args);
    else
        return $format;
}
function sugar_function_sprintf($s, $p)
{
    return sugar_function_printf($s, $p);
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
 *+   {% default value=$value default=$default %}
 *+ is equivalent to
 *+   {% if $value ; $value ; else ; $default ; end %}
 *+
 *+ This is particularly useful for the value attribute for form
 *+ input tags when used in conjunction with a user-input value
 *+ and the form's default value.
 */
function sugar_function_default($sugar, $params)
{
    $value = SugarUtil::getArg($params, 'value');
    if ($value)
        return $value;
    else
        return SugarUtil::getArg($params, 'default');
}

/*++
 *+ @name count 
 *+ @param array $array Array to count.
 *+ @return int Number of elements in the array.
 *+
 *+ Returns the number of elements within the given array,
 *+ using the internal PHP count() function.
 */
function sugar_function_count($sugar, $params)
{
    return count(SugarUtil::getArg($params, 'array'));
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
 *+   <option {% selected test=$value=='First' %}>First</option>
 *+   <option {% selected test=$value=='Second' %}>Second</option>
 *+   <option {% selected test=$value=='Third' %}>Third</option>
 */
function sugar_function_selected($sugar, $params)
{
    if (SugarUtil::getArg($params, 'test'))
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
 *+   <input type="checkbox" name="first" {% checked test=$first=='on' %}>
 *+   <input type="checkbox" name="second" {% checked test=$second=='on' %}>
 */
function sugar_function_checked($sugar, $params)
{
    if (SugarUtil::getArg($params, 'test'))
        return new SugarEscaped(' checked="checked" ');
}

/*++
 *+ @name disabled
 *+ @param mixed $test The test expression.
 *+ @return string The string ' disabled="disabled" ' if $test is true.
 *+
 *+ If the given input is a true value, then the HTML attribute code
 *+ disabled="disabled" is returned.
 *+
 *+ This is useful to use inside of HTML input elements. e.g.
 *+   <input type="checkbox" name="first" {% disabled test=$first=='on' %}>
 *+   <input type="checkbox" name="second" {% disabled test=$second=='on' %}>
 */
function sugar_function_disabled($sugar, $params)
{
    if (SugarUtil::getArg($params, 'test'))
        return new SugarEscaped(' disabled="disabled" ');
}

/*++
 *+ @name select 
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
 *+   $n = 'foo'; select value=$n default='not found' foo='Found Foo' bar='Found Bar'
 *+   // would print 'Found Foo'
 */
function sugar_function_select($sugar, $params)
{
    $value = SugarUtil::getArg($params, 'value');
    $default = SugarUtil::getArg($params, 'default', $value);

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
function sugar_function_truncate($sugar, $params)
{
    $text = (string)SugarUtil::getArg($params, 'string');
    $length = (int)SugarUtil::getArg($params, 'length', 72);
    $end = (string)SugarUtil::getArg($params, 'end', '...');
    if (strlen($text) <= $length)
        return $text;
    else
        return preg_replace('/\s+?(\S+)?$/', $end, substr($text, 0, $length - strlen($end) + 1));
}

/*++
 *+ @name escape
 *+ @type modifier
 *+ @param mixed $value String (or any other type) to escape.
 *+ @param string $mode Escape format to use.  (default 'html')
 *+ @return raw Escaped value.
 *+
 *+ Mode must be one of 'html', 'xml', 'json', or 'url'.
 *+
 *+ The input value is escaped according to the rules of $mode, resulting
 *+ in a raw string which can be safely printed out.
 *+
 *+ For the mode 'json', this is equivalent to:
 *+   {% json value=$value %}
 *+
 *+ For the mode 'url', this is equivalent to:
 *+   {% urlencode string=$value %}
 *+
 *+ For the modes 'html' and 'xml', this is equivalent to the default
 *+ output encoding rules for both languages.
 */
function sugar_modifier_escape($value, $sugar, $params)
{
    $mode = isset($params[0]) ? (string)$params[0] : 'html';

    switch ($mode) {
    case 'html':
        return new SugarEscaped(htmlentities($value, ENT_QUOTES, $sugar->charset));
    case 'xhtml':
    case 'xml':
        return new SugarEscaped(htmlspecialchars($value, ENT_QUOTES, $sugar->charset));
    case 'javascript':
    case 'js':
    case 'json':
        return new SugarEscaped(SugarUtil::json($value));
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
 *+   {% $foo %}
 *+   {% var name='foo' %}
 *+   {% $name = 'foo' ; var name=$name %}
 */
function sugar_function_var($sugar, $params)
{
    $name = (string)SugarUtil::getArg($params, 'name');
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
function sugar_function_array($sugar, $params)
{
    return $params;
}

/*++
 *+ @name strtolower
 *+ @param string $string The string to process.
 *+ @return string $string with all characters lower-cased.
 *+
 *+ Equivalent to PHP's strtolower().
 */
function sugar_function_strtolower($sugar, $params)
{
    return strtolower((string)SugarUtil::getArg($params, 'string'));
}

/*++
 *+ @name strtoupper
 *+ @param string $string The string to process.
 *+ @return string $string with all characters upper-cased.
 *+
 *+ Equivalent to PHP's strtoupper().
 */
function sugar_function_strtoupper($sugar, $params)
{
    return strtoupper((string)SugarUtil::getArg($params, 'string'));
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
 *+   substr string='hello world' start=2 length=6 // llo wo
 *+   substr string='hello world' start=0 length=5 // hello
 *+   substr string='hello world' start=6 length=5 // world
 *+   substr string='hello world' start=10 length=5 // ld
 */
function sugar_function_substr($sugar, $params)
{
    $string = (string)SugarUtil::getArg($params, 'string');
    $start = (int)SugarUtil::getArg($params, 'start');
    $length = (int)SugarUtil::getArg($params, 'length');
    return substr($string, $start, $length);
}

/*++
 *+ @name nl2br
 *+ @param string $string The string to process.
 *+ @return raw $string all newlines replaced with '<br />' and all HTML special characters escaped.
 *+
 *+ Equivalent to calling Sugar::escape() followed by PHP's nl2br().
 */
function sugar_function_nl2br($sugar, $params)
{
    $string = SugarUtil::getArg($params, 'string');
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
 *+  <tr class="row{% cycle %}">
 */
function sugar_function_cycle($sugar, $params)
{
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
function sugar_function_isset($sugar, $params)
{
    $obj = SugarUtil::getArg($params, 'object');
    $index = SugarUtil::getArg($params, 'index');
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
function sugar_function_time($sugar, $params)
{
    return time();
}

/*++
 *+ @name basename
 *+ @param string $path File path name.
 *+ @return string Just the file portion of $path.
 *+
 *+ Equivalent to PHP's basename().
 */
function sugar_function_basename($sugar, $params)
{
    return basename(SugarUtil::getArg($params, 'path'));
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
function sugar_function_merge($sugar, $params)
{
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
function sugar_function_join($sugar, $params)
{
    $sep = (string)SugarUtil::getArg($params, 'separator', ' ');
    $array = (array)SugarUtil::getArg($params, 'array');
    return implode($sep, $array);
}
function sugar_function_implode($s, $p)
{
    return sugar_function_join($s, $p);
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
function sugar_function_split($sugar, $params)
{
    $sep = (string)SugarUtil::getArg($params, 'delimiter', ' ');
    $string = (string)SugarUtil::getArg($params, 'string');
    $count = (int)SugarUtil::getArg($params, 'count');
    if ($count > 0)
        return explode($sep, $string, $count);
    else
        return explode($sep, $string);
}
function sugar_function_explode($s, $p)
{
    return sugar_function_split ($s, $p);
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
 *+   {% psplit expr='/\s+/' string=$string %}
 *+
 *+ Equivalent to PHP's preg_split().
 */
function sugar_function_psplit($sugar, $params)
{
    $expr = (string)SugarUtil::getArg($params, 'expr', '/\s+/');
    $string = (string)SugarUtil::getArg($params, 'string');
    $count = (int)SugarUtil::getArg($params, 'count');
    return preg_split($expr, $string, $count);
}

/*++
 *+ @name int 
 *+ @type modifier
 *+ @param mixed $value Value to convert.
 *+ @return int Value converted to an integer.
 *+
 *+ Converts the input value into an integer.
 *+
 *+ Equivalent to PHP's intval().
 */
function sugar_modifier_int($value)
{
    return intval($value);
}

/*++
 *+ @name upper 
 *+ @type modifier
 *+ @param string $value String to convert.
 *+ @return string String with all letters converted to upper case.
 *+
 *+ Converts a string to all upper case letters.
 *+
 *+ Equivalent to PHP's strtoupper().
 */
function sugar_modifier_upper($value)
{
    return strtoupper($value);
}

/*++
 *+ @name lower 
 *+ @type modifier
 *+ @param string $value String to convert.
 *+ @return string String with all letters converted to lower case.
 *+
 *+ Converts a string to all lower case letters.
 *+
 *+ Equivalent to PHP's strtolower().
 */
function sugar_modifier_lower($value)
{
    return strtolower($value);
}

/*++
 *+ @name default
 *+ @type modifier
 *+ @param mixed $value Value to test.
 *+ @param mixed $default Value to use if $value is unset.
 *+ @return mixed $value, or $default if $value is null.
 *+
 *+ If $value is not null, return $value, otherwise return $default.
 *+
 *+ Example:
 *+   {% $undefined |default:'Not Defined' %}
 */
function sugar_modifier_default($value, $sugar, $params) {
    if (is_null($value) && isset($params[0]))
        return $params[0];
    else
        return $value;
}

/*++
 *+ @name ldelim
 *+ @return string Left delimiter.
 *+
 *+ Returns the left delimiter token.
 */
function sugar_function_ldelim($sugar, $params) {
    return $sugar->delimStart;
}

/*++
 *+ @name rdelim
 *+ @return string Right delimiter.
 *+
 *+ Returns the right delimiter token.
 */
function sugar_function_rdelim($sugar, $params) {
    return $sugar->delimEnd;
}

/**#@-*/

// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
