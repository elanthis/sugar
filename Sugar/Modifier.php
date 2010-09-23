<?php
/**
 * Modifier plugin base class.
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
 * @subpackage Plugin
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2010 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    SVN: $Id: Lexer.php 320 2010-08-25 08:15:44Z Sean.Middleditch $
 * @link       http://php-sugar.net
 * @access     private
 */

/**
 * Modifier plugin base class.
 *
 * @category   Template
 * @package    Sugar
 * @subpackage Plugin
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2010 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    Release: 0.84
 * @link       http://php-sugar.net
 * @access     private
 */
abstract class Sugar_Modifier
{
    /**
     * Whether or not the result should be auto-escaped
     *
     * @var boolean
     */
    private $_escape = true;

    /**
     * Set flag controlling whether modifier result should be auto-escaped
     *
     * @param $cacheable boolean
     */
    final public function setEscape($escape)
    {
        $this->_escape = $escape;
    }

    /**
     * Check if the modifier result should be auto-escaped
     *
     * @return boolean
     */
    final public function getEscape()
    {
        return $this->_escape;
    }

    /**
     * Execute the modifier
     *
     * @param mixed         $value   Value to modify.
     * @param array         $params  Parameters
     * @param Sugar_Context $context Execution context
     * @return mixed Modifier result
     */
    abstract function invoke($value, array $params, Sugar_Context $context);
}

/**
 * Modifier plugin wrapper class.
 *
 * @category   Template
 * @package    Sugar
 * @subpackage Plugin
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2010 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    Release: 0.84
 * @link       http://php-sugar.net
 * @access     private
 */
final class Sugar_ModifierWrapper extends Sugar_Modifier
{
    /**
     * Modifier callable
     *
     * @var callable
     */
    public $callable;

    /**
     * Execute the modifier
     *
     * @param mixed         $value   Value to modify.
     * @param array         $params  Parameters
     * @param Sugar_Context $context Execution context
     * @return mixed Modifier result
     */
    public function invoke($value, array $params, Sugar_Context $context)
    {
        return call_user_func($this->callable, $value, $context->getSugar(), $params, $context);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
