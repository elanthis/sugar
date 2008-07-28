<?php
/**
 * SugarEscaped helper class.
 *
 * A simple class for wrapping strings, informing the Sugar runtime that the
 * strings should not be escaped.
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
 * @subpackage Exceptions
 * @author Sean Middleditch <sean@mojodo.com>
 * @copyright 2008 Mojodo, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 * @version 0.80
 * @link http://php-sugar.net
 */

/**
 * Encapsulates a string, inhibiting the default HTML escaping when printed.
 *
 * @category Template
 * @package Sugar
 * @author Sean Middleditch <sean@mojodo.com>
 * @copyright 2008 Mojodo, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 * @version 0.80
 * @link http://php-sugar.net
 */
class SugarEscaped
{
    /**
     * Encapsulated text.
     *
     * @var string $text
     */
	private $text;

    /**
     * Constructor.
     *
     * @param string $text Text to encapsulate.
     */
	public function __construct($text)
	{
		$this->text = $text;
	}

    /**
     * Retrieves the encapsulated text.
     *
     * @return string Text.
     */
	public function getText()
	{
		return $this->text;
	}

    /**
     * Allows automatic conversion to a string for string operations.
     *
     * @return string Text.
     */
	public function __toString()
	{
		return $this->text;
	}
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
