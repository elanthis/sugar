<?php
/**
 * Function plugin base class.
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
 * Function plugin base class.
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
abstract class Sugar_Function
{
    /**
     * Sugar object
     *
     * @var Sugar
     */
    public $sugar;

    /**
     * Whether or not the output should be escaped
     *
     * @var boolean
     */
    public $escape = true;

    /**
     * Whether or not the function is cacheable
     *
     * @var boolean
     */
    public $cache = true;

    /**
     * Constructor
     *
     * @param Sugar $sugar
     */
    final public function __construct(Sugar $sugar)
    {
        $this->sugar = $sugar;
    }

    /**
     * Execute the function
     *
     * @param array $params Parameters
     * @return mixed Function result
     */
    abstract function invoke(array $params);
}

/**
 * Function plugin invocable wrapper class.
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
final class Sugar_FunctionWrapper extends Sugar_Function
{
    /**
     * The callable for this function
     *
     * @var callable
     */
    public $callable;

    /**
     * Execute the function
     *
     * @param array $params Parameters
     * @return mixed Function result
     */
    public function invoke(array $params)
    {
        return call_user_func($this->callable, $this->sugar, $params);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
