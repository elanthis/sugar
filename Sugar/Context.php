<?php
/**
 * Class for managing runtime contexts
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
 * @subpackage Runtime
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2010 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    SVN: $Id: Lexer.php 320 2010-08-25 08:15:44Z Sean.Middleditch $
 * @link       http://php-sugar.net
 * @access     private
 */

/**
 * Runtime context.
 *
 * Tracks current variable data, current template, and cache handler.
 *
 * @category   Template
 * @package    Sugar
 * @subpackage Runtime
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2010 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    Release: 0.84
 * @link       http://php-sugar.net
 * @access     private
 */
final class Sugar_Context
{
    /**
     * Sugar context
     *
     * @var Sugar
     */
    private $_sugar;

    /**
     * Template
     *
     * @var Sugar_Template
     */
    private $_template;

    /**
     * Variable data
     *
     * @var Sugar_Data
     */
    private $_data;

    /**
     * Runtime instance
     *
     * @var Sugar_Runtime
     */
    private $_runtime;

    /**
     * Create instance
     *
     * @param Sugar          $sugar    Sugar instance
     * @param Sugar_Template $template Template being evaluated
     * @param Sugar_Data     $data     Variable data for execution
     * @param Sugar_Runtime  $runtime  Runtime instance
     */
    public function __construct(Sugar $sugar, Sugar_Template $template, Sugar_Data $data, Sugar_Runtime $runtime) {
        $this->_sugar = $sugar;
        $this->_template = $template;
        $this->_data = $data;
        $this->_runtime = $runtime;
    }

    /**
     * Get the context's Sugar intance
     *
     * @return Sugar
     */
    public function getSugar()
    {
        return $this->_sugar;
    }

    /**
     * Get the template being executed.
     *
     * @return Sugar_Template
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    /**
     * Get the variable data in use.
     *
     * @return Sugar_Data
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Get runtime instance
     *
     * @return Sugar_Runtime
     */
    public function getRuntime()
    {
        return $this->_runtime;
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
