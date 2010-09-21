<?php
/**
 * Contains the compiled version of a template, which may be a cached
 * template.
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
 * Contains the compiled version of a template, which may be a cached
 * template.
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
final class Sugar_Compiled
{
    /**
     * Template to inherit from
     *
     * @var string
     */
    private $_inherit;

    /**
     * Sections
     *
     * @var array
     */
    private $_sections;

    /**
     * "Bytecode" data
     *
     * @var array
     */
    private $_code;

    /**
     * Create instance
     *
     * @param string $inherit  Name of template to inherit from ('' for none)
     * @param array  $code     Main code
     * @param array  $sections Sections defined in template
     */
    public function __construct($inherit, array $code, array $sections) {
        $this->_inherit = $inherit;
        $this->_code = $code;
        $this->_sections = $sections;
    }

    /**
     * Get the inherited template
     *
     * @return string
     */
    public function getInherit()
    {
        return $this->_inherit;
    }

    /**
     * Get the main code
     *
     * @return array
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Get a particular section by name
     *
     * @param string $name Name of section to get
     * @return mixed Code for section if it exists, false otherwise
     */
    public function getSection($name)
    {
        if (isset($this->_sections[$name])) {
            return $this->_sections[$name];
        } else {
            return false;
        }
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
