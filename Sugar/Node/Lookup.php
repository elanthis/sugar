<?php
/**
 * Sugar compiler lookup expression node
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
 * Sugar variable lookup node.
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
class Sugar_Node_Lookup extends Sugar_Node
{
    /**
     * Variable name
     *
     * @var string 
     */
    public $name;

    /**
     * Returns false, as this is not literal
     *
     * @return boolean false 
     */
    public function isLiteral()
    {
        return false;
    }

    /**
     * Returns false, not escaped by default
     *
     * @return boolean false
     */
    public function isEscaped()
    {
        return false;
    }

    /**
     * Returns compiled bytecode array for expression
     *
     * @return array Compiled bytecode.
     */
    public function compile()
    {
        return array('lookup', $this->name);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
