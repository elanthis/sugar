<?php
/**
 * Sugar compiler node.
 *
 * This is a small helper class used by the Sugar_Grammar class.
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
 * @subpackage Compiler
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2010 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    SVN: $Id: Lexer.php 320 2010-08-25 08:15:44Z Sean.Middleditch $
 * @link       http://php-sugar.net
 * @access     private
 */

/**
 * Sugar compiler node base class.
 *
 * @category   Template
 * @package    Sugar
 * @subpackage Compiler
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2010 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    Release: 0.84
 * @link       http://php-sugar.net
 * @access     private
 */
abstract class Sugar_Node
{
    /**
     * Sugar object
     *
     * @var Sugar
     */
    protected $_sugar;

    /**
     * Constructor
     *
     * @param Sugar $sugar
     */
    final public function __construct(Sugar $sugar)
    {
        $this->_sugar = $sugar;
    }

    /**
     * Test if expression node represents a literal value
     *
     * @return boolean True if node is a literal
     */
    abstract public function isLiteral();

    /**
     * Check whether the node's result should be auto-escaped
     *
     * @return boolean True if it should be escaped when printed
     */
    abstract public function getEscape();

    /**
     * Returns compiled bytecode array for expression
     *
     * @return array Compiled bytecode.
     */
    abstract public function compile();
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
