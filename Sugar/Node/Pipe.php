<?php
/**
 * Sugar modifier (pipe operator) node
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
 * Sugar modifier (pipe operator) node.
 *
 * Represents a series of modifiers applied to an expression in the compiler tree.
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
class Sugar_Node_Pipe extends Sugar_Node
{
    /**
     * Base node
     *
     * @var Sude_Node
     */
    public $node;

    /**
     * Modifiers to apply
     *
     * @var array
     */
    public $modifiers = array();

    /**
     * Return false, as modified nodes are not a literal.
     *
     * @return boolean false
     */
    public function isLiteral()
    {
        return false;
    }

    /**
     * Checks if the last modifier is an escape modifier
     *
     * @return boolean true if last modifier is an esape modifier
     */
    public function isEscaped()
    {
        // get last modifier's name
        $name = $this->modifiers[count($this->modifiers) - 1]['name'];

        // if the built-in escape or raw modifier is used, we are escaped
        if ($name == 'escape' || $name == 'raw') {
            return true;
        }

        // no other modifer is escaped by default
        return false;
    }

    /**
     * Returns compiled bytecode array for expression
     *
     * @return array Compiled bytecode.
     */
    public function compile()
    {
        // resulting opcodes
        $opcodes = array($this->node->compile());

        // compile modifiers
        $cmodifiers = array();
        foreach ($this->modifiers as $mod) {
            // compile modifier parameters
            $cparams = array();
            foreach ($mod['params'] as $node) {
                $cparams []= $node->compile();
            }

            // append modifier invocation to opcodes
            $opcodes []= array(Sugar_Runtime::OP_MODIFY, $mod['name'], $cparams);
        }

        // merge resulting output
        return call_user_func_array('array_merge', $opcodes);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
