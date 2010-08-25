<?php
/**
 * Sugar template language token class.
 *
 * This is a small helper class used by the Sugar_Lexer class.
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
 * Sugar language token.
 *
 * Represents a single lexical token from the input source.
 *
 * @category   Template
 * @package    Sugar
 * @subpackage Compiler
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2010 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    Release: 0.83
 * @link       http://php-sugar.net
 * @access     private
 */
class Sugar_Token
{
    /**
     * Type of the token.
     *
     * @var string
     */
    public $type;

    /**
     * Line the token was encountered on.
     *
     * @var int
     */
    public $line;

    /**
     * Extra data for the token, such as identifier name.
     *
     * @var mixed
     */
    public $extra;

    /**
     * Create instance
     *
     * @param string $type  Token type
     * @param int    $line  Line number of token
     * @param mixed  $extra Type-specific data
     */
    public function __construct($type, $line, $extra) {
        $this->type = $type;
        $this->line = $line;
        $this->extra = $extra;
    }

    /**
     * Returns a user-friendly name for a token, used for error messages.
     *
     * @return string Nice name for the token.
     */
    public function getPrettyName()
    {
        switch($this->type) {
        case 'eof': return '<eof>';
        case 'id': return 'identifier '.$this->extra;
        case 'var': return 'variable $'.$this->extra;
        case 'data':
            if (is_string($this->extra)) {
                return 'string "'.addslashes($this->extra).'"';
            } elseif (is_float($this->extra)) {
                return 'float '.$this->extra;
            } elseif (is_int($this->extra)) {
                return 'integer '.$this->extra;
            } elseif (is_object($this->extra)) {
                return 'object '.get_class($this->extra);
            } else {
                return gettype($this->extra);
            }
        case 'term': return $this->extra;
        case 'end-block': return '/'.$this->extra;
        default: return $this->type;
        }
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
