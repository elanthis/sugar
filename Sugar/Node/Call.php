<?php
/**
 * Sugar function/method invocation node
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
 * Sugar function/method invocation node
 *
 * Represents a function or method call.
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
class Sugar_Node_Call extends Sugar_Node
{
    /**
     * Expression operator (either 'call' for a function or
     * 'method' for a method)
     *
     * @var string
     */
    public $operator;

    /**
     * Function/method name
     *
     * @var string
     */
    public $name;

    /**
     * File of invocation
     *
     * @var string
     */
    public $file;

    /**
     * Line number in file of invocation
     *
     * @var integer
     */
    public $line;

    /**
     * Parameters
     *
     * @var array
     */
    public $params = array();

    /**
     * Return false, as an invocation is not a literal.
     *
     * @return boolean false
     */
    public function isLiteral()
    {
        return false;
    }

    /**
     * Checks if the function is escaped by default.
     *
     * Note that methods are never escaped by default
     *
     * @return boolean True if an escaped function, false otherwise
     */
    public function isEscaped()
    {
        // methods are never escaped by default
        if ($this->operator != 'call') {
            return false;
        }

        // load the requested function
        $func = $this->_sugar->getFunction($this->name);
        if (!$func) {
            return false;
        }

        // if the function has escaping disabled, then treat the
        // function return value as if it is escaped
        return !$func['escape'];
    }

    /**
     * Returns compiled bytecode array for expression
     *
     * @return array Compiled bytecode.
     */
    public function compile()
    {
        // compile parameters
        $cparams = array();
        foreach ($this->params as $name=>$node) {
            $cparams [$name]= $node->compile();
        }

        // return full expression
        return array($this->operator, $this->name, $cparams,
            $this->file, $this->line);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
