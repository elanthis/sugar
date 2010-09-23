<?php
/**
 * Sugar function invocation node
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
 * Sugar function invocation node
 *
 * Represents a function call.
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
     * Function name
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
     * Check if the function's result should be auto-escaped
     *
     * @return boolean
     */
    public function getEscape()
    {
        // load the requested function
        $plugin = $this->_sugar->getPlugin('function', $this->name);
        if (!$plugin) {
            return true;
        }

        // if the plugintion has escaping disabled, then treat the
        // plugintion return value as if it is escaped
        return $plugin->getEscape();
    }

    /**
     * Returns compiled bytecode array for expression
     *
     * @return array Compiled bytecode.
     */
    public function compile()
    {
        $opcodes = array();

        // compile parameters
        foreach ($this->params as $name=>$node) {
            $opcodes []= array(Sugar_Runtime::OP_PUSH, $name);
            $opcodes []= $node->compile();
        }

        // append function call opcode
        $opcodes []= array(Sugar_Runtime::OP_CALL, $this->name,
            count($this->params), $this->file, $this->line);

        // return opcodes
        return call_user_func_array('array_merge', $opcodes);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
