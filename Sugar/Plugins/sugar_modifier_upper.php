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
 * @category   Template
 * @package    Sugar
 * @subpackage Stdlib
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2008-2009 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    SVN: $Id$
 * @link       http://php-sugar.net
 */

/**
 * Standard library modifier
 *
 * Sugar template modifier.  Please view the Sugar reference manual
 * for documentation on the behavior and use of these functions from
 * within templates.
 *
 * @param mixed $value  Value being modified.
 *
 * @return mixed
 */
function sugar_modifier_upper($value)
{
    return strtoupper($value);
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
