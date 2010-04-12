<?php
/**
 * Sugar template language tokenizer.
 *
 * The tokenizer is responsible for taking template files and breaking them
 * into a series of tokens to be consumed by the grammar parser portion of
 * the Sugar compiler.  The tokenizer makes use of some rather ugly
 * regular expressions as they actually provide better performance than any
 * other method available in PHP for this purpose.
 *
 * The regular expressions could possible use some improvements in efficiency.
 * In particular, even though the compiled regex is cached by PHP, the string
 * interpolation done on each token loop to build the regex should be avoied.
 * This is the most used and most performance-sensitive portion of the
 * compiler, and as such needs more love than the grammar when it comes time
 * to optimize.
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
 * @copyright  2008-2009 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    SVN: $Id$
 * @link       http://php-sugar.net
 * @access     private
 */

/**
 * Sugar language tokenizer.
 *
 * Tokenizes a source file for use by {@link SugarGrammar}.
 *
 * @category   Template
 * @package    Sugar
 * @subpackage Compiler
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2008-2009 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    Release: 0.83
 * @link       http://php-sugar.net
 * @access     private
 */
class SugarLexer
{
    /**
     * Source code to be tokenized.
     *
     * @var string
     */
    private $_src;

    /**
     * Next token.
     *
     * @var array
     */
    private $_token = null;

    /**
     * Index into the source code.
     *
     * @var int $_pos
     */
    private $_pos = 0;

    /**
     * Flag indicating whether the tokenizer is currently working
     * within a pair of delimiters.
     *
     * @var bool
     */
    private $_inCmd = false;

    /**
     * The name of the source file being tokenized.
     *
     * @var string
     */
    private $_file;

    /**
     * Current line number of the source being tokenized.
     *
     * @var int
     */
    private $_line = 1;

    /**
     * Line number of the next token.
     *
     * @var int
     */
    private $_tokline;

    /**
     * Starting delimiter.
     *
     * @var string
     */
    private $_delimStart = '<%';

    /**
     * Ending delimiter.
     *
     * @var string
     */
    private $_delimEnd = '%>';

    /**
     * Constructor.
     *
     * @param string $src        The source code to tokenizer.
     * @param string $file       The name of the file being tokenized.
     * @param string $delimStart Start delimiter for template code.
     * @param string $delimEnd   End delimiter for template code.
     */
    public function __construct($src, $file, $delimStart, $delimEnd)
    {
        $this->_src = $src;
        $this->_file = $file;
        $this->_delimStart = $delimStart;
        $this->_delimEnd = $delimEnd;

        $this->_token = $this->_next();
    }

    /**
     * Returns a user-friendly name for a token, used for error messages.
     *
     * @param array $token Token to name.
     *
     * @return string Nice name for the token.
     */
    public static function tokenName($token)
    {
        switch($token[0]) {
        case 'eof': return '<eof>';
        case 'id': return 'identifier '.$token[1];
        case 'var': return 'variable $'.$token[1];
        case 'data':
            if (is_string($token[1])) {
                return 'string "'.addslashes($token[1]).'"';
            } elseif (is_float($token[1])) {
                return 'float '.$token[1];
            } elseif (is_int($token[1])) {
                return 'integer '.$token[1];
            } elseif (is_object($token[1])) {
                return 'object '.get_class($token[1]);
            } else {
                return gettype($token[1]);
            }
        case 'term': return $token[1];
        case 'end-block': return '/'.$token[1];
        default: return $token[0];
        }
    }

    /**
     * Converts backslash escape sequences in strings to the proper value.
     * Only supports double-backslash and backslash-n (newline) currently.
     *
     * @param string $string String to decode.
     *
     * @return string Decoded string.
     */
    public static function decodeSlashes($string)
    {
        $string = str_replace('\\n', "\n", $string);
        $string = stripslashes($string);
        return $string;
    }

    /**
     * Checks to see if the requested regular expression matches at the
     * current position of the source file, and returns the matched
     * expressions if it does.  Additionally, this moves the source
     * position forward by the length of the matched expression and updates
     * the line count as appropriate.
     *
     * @param string $regex Regular expression to check for.
     *
     * @return mixed Array of subexpression matches on successful, or false
     *               if no match.
     */
    private function _getRegex($regex)
    {
        if (!preg_match($regex, $this->_src, $ar, 0, $this->_pos)) {
            return false;
        }
        $this->_pos += strlen($ar[0]);
        $this->_line += substr_count($ar[0], "\n");
        return $ar;
    }

    /**
     * Retrieves the next token in the input stream.
     *
     * @return array Next token.
     * @throws Sugar_Exception_Parse on invalid template input.
     */
    private function _next()
    {
        // EOF
        if ($this->_pos >= strlen($this->_src)) {
            return array('eof', null);
        }

        // outside of a command?
        if (!$this->_inCmd) {
            // find next opening delimiter
            $next = strpos($this->_src, $this->_delimStart, $this->_pos);

            // set $next to last byte
            if ($next === false) {
                $next = strlen($this->_src);
            }

            // just a literal?
            if ($next > $this->_pos) {
                $text = substr($this->_src, $this->_pos, $next - $this->_pos);
                $this->_tokline = $this->_line;
                $this->_line += substr_count(
                    $this->_src,
                    "\n",
                    $this->_pos,
                    $next - $this->_pos
                );
                $this->_pos = $next;
                return array('literal', $text);
            }

            // setup inside command
            $this->_inCmd = true;
            $this->_pos = $next + 2;
        }

        // skip spaces and comments
        while (($ar = $this->_getRegex('/(?:\s+|(?:\/\*.*?\*\/|\/\/.*?($|'.preg_quote($this->_delimEnd).')))/msA')) !== false) {
            // line comment ended with an end delimiter
            if (isset($ar[1]) && $ar[1] === $this->_delimEnd) {
                $this->_inCmd = false;
                return array('term', $this->_delimEnd);
            }
        }

        // get next token
        $this->_tokline = $this->_line;
        if (($token = $this->_getRegex('/(?:'.preg_quote($this->_delimEnd).'|\$(\w+)|(\d+(?:[.]\d+)?)|\/([A-Za-z_]\w+)|(\w+)|==|!=|!in\b|<=|>=|\|\||&&|->|[.][.]|.)/msA')) === false) {
            throw new Sugar_Exception_Parse(
                $this->_file,
                $this->_line,
                'garbage at: '.substr($this->_src, $this->_pos, 12)
            );
        }

        // if at end, mark that
        if ($token[0] === $this->_delimEnd) {
            $this->_inCmd = false;
        }

        // string
        if ($token[0] === '"') {
            if (($string = $this->_getRegex('/((?:[^"\\\\]*\\\\.)*[^"]*)"/msA')) === false) {
                throw new Sugar_Exception_Parse(
                    $this->_file,
                    $this->_line,
                    'unterminated string constant at: '.
                        substr($this->_src, $this->_pos, 12)
                );
            }
            return array('data', self::decodeSlashes($string[1]));
        } elseif ($token[0] === '\'') {
            if (($string = $this->_getRegex('/((?:[^\'\\\\]*\\\\.)*[^\']*)\'/msA')) === false) {
                throw new Sugar_Exception_Parse(
                    $this->_file,
                    $this->_line,
                    'unterminated string constant at: '.
                        substr($this->_src, $this->_pos, 12)
                );
            }
            return array('data', self::decodeSlashes($string[1]));
        }

        // variable
        if (isset($token[1]) && $token[1] !== '') {
            return array('var', $token[1]);
        } elseif ($token[0] === $this->_delimEnd || $token[0] === ';') {
            // statement terminator
            return array('term', $token[0]);
        } elseif (isset($token[4]) && in_array($token[4], array(
            'if', 'elif', 'else', 'end', 'foreach', 'in', 'loop',
            'while', 'nocache', 'section', 'insert'))
        ) {
            // keyword or special symbol
            return array($token[4], null);
        } elseif (isset($token[3]) && $token[3] !== '') {
            // block terminator
            return array('end-block', $token[3]);
        } elseif (isset($token[2]) && $token[2] !== ''
            && strpos($token[2], '.') !== false
        ) {
            // floating point number
            return array('data', floatval($token[2]));
        } elseif (isset($token[2]) && $token[2] !== '') {
            // integer
            return array('data', intval($token[2]));
        } elseif ($token[0] === 'and') {
            // and and or
            return array('&&', null);
        } elseif ($token[0] === 'or') {
            return array('||', null);
        } elseif ($token[0] === 'true') {
            // true and false
            return array('data', true);
        } elseif ($token[0] === 'false') {
            return array('data', false);
        } elseif (isset($token[4]) && $token[4] !== '') {
            // name
            return array('id', $token[4]);
        } else {
            // generic operator
            return array($token[0], null);
        }
    }

    /**
     * Checks to the see if the next token matches the requested token
     * type.  If it does, the token is consumed.  The token data is
     * stored in the second parameter.  True is returned if the token
     * was consumed, and false otherwise.
     *
     * @param string $accept Which token type to accept.
     * @param mixed  &$data  Token token.
     *
     * @return bool True if the token matched.
     */
    public function accept($accept, &$data = null)
    {
        // return false if it's the wrong token
        if ($this->_token[0] != $accept) {
            return false;
        }

        // store data
        $data = $this->_token[1];

        // get next token
        $this->_token = $this->_next();
        return true;
    }

    /**
     * Checks to see if the next token is one of a list of given types.
     *
     * @param array $accept Tokens to accept.
     *
     * @return bool True if one of the given token types matches.
     */
    public function peekAny(array $accept)
    {
        return in_array($this->_token[0], $accept);
    }

    /**
     * Checks to the see if the next token matches the requested token
     * type.  If it does, the token is consumed.  The token data is
     * stored in the second parameter.  If the token does not match,
     * a {@link Sugar_Exception_Parse} is raised.
     *
     * @param mixed $expect Which token type to accept, or a list of tokens.
     * @param mixed &$data  Token token.
     *
     * @return bool True on success.
     * @throws Sugar_Exception_Parse when the next token does not match $accept.
     */
    public function expect($expect, &$data = null)
    {
        // throw an error if it's the wrong token
        if (is_array($expect)) {
            if (!in_array($this->_token[0], $expect)) {
                throw new Sugar_Exception_Parse(
                    $this->_file,
                    $this->_tokline,
                    'expected '.implode(' or ', $expect).  '; found '.
                        self::tokenName($this->_token)
                );
            }
        } else {
            if ($this->_token[0] != $expect) {
                throw new Sugar_Exception_Parse(
                    $this->_file,
                    $this->_tokline,
                    'expected '.$expect.  '; found '.
                        self::tokenName($this->_token)
                );
            }
        }

        // store value
        $data = $this->_token[1];

        // get next token
        $this->_token = $this->_next();
        return true;
    }

    /**
     * Block terminator expect wrapper.
     *
     * @param string $name Block name to expect.
     *
     * @return bool True on success.
     * @throws Sugar_Exception_Parse
     */
    public function expectEndBlock($name)
    {
        if ($this->_token[0] != 'end'
            && ($this->_token[0] != 'end-block' || $this->_token[1] != $name)
        ) {
            throw new Sugar_Exception_Parse(
                $this->_file,
                $this->_tokline,
                'expected /'.$name.'; found '.self::tokenName($this->_token));
        }

        // get next token
        $this->_token = $this->_next();
        return true;
    }

    /**
     * Similar to {@link SugarLexer::expect}, except that it checks for
     * any of standard Sugar operators, and the matched operator (if any)
     * is returned.
     *
     * @return mixed The operator if one matches, or false otherwise.
     */
    public function getOp()
    {
        $op = $this->_token[0];

        // convert = to == for operators
        if ($op == '=') {
            $op = '==';
        }

        // if it's a valid operator, return it
        if (isset(SugarGrammar::$precedence[$op])) {
            // get next token
            $this->_token = $this->_next();

            return $op;
        } else {
            return false;
        }
    }

    /**
     * Returns the current line the tokenizer is at.
     *
     * @return int Line number.
     */
    public function getLine()
    {
        return $this->_line;
    }

    /**
     * Returns the file name given to the constructor.
     *
     * @return string File name.
     */
    public function getFile()
    {
        return $this->_file;
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
