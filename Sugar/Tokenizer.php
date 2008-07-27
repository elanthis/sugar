<?php
/**
 * PHP-Sugar Template Engine
 *
 * Copyright (c) 2008  AwesomePlay Productions, Inc. and
 * contributors.  All rights reserved.
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
 * @package Sugar
 * @subpackage Internals
 * @author Sean Middleditch <sean@awesomeplay.com>
 * @copyright 2008 AwesomePlay Productions, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

/**
 * Tokenizes a source file for use by {@link SugarParser}.
 *
 * @package Sugar
 * @subpackage Internals
 */
class SugarTokenizer {
    /**
     * Source code to be tokenized.
     *
     * @var string $src
     */
    private $src;

    /**
     * Next token.
     *
     * @var array $token
     */
    private $token = null;

    /**
     * Index into the source code.
     *
     * @var int $pos
     */
    private $pos = 0;

    /**
     * Flag indicating whether the tokenizer is currently working
     * within a pair of delimiteres.
     *
     * @var bool $inCmd
     */
    private $inCmd = false;

    /**
     * The name of the source file being tokenized.
     *
     * @var string $file
     */
    private $file;

    /**
     * Current line number of the source being tokenized.
     *
     * @var int $line
     */
    private $line = 1;

    /**
     * Line number of the next token.
     *
     * @var int $tokline
     */
    private $tokline;

    /**
     * Starting delimiter.
     *
     * @var string
     */
    private $delimStart = '<%';

    /**
     * Ending delimiter.
     *
     * @var string
     */
    private $delimEnd = '%>';

    /**
     * Constructor.
     *
     * @param string $src The source code to tokenizer.
     * @param string $file The name of the file being tokenized.
     */
    public function __construct ($src, $file, $delimStart, $delimEnd) {
        $this->src = $src;
        $this->file = $file;
	$this->delimStart = $delimStart;
	$this->delimEnd = $delimEnd;

        $this->token = $this->next();
    }

    /**
     * Returns a user-friendly name for a token, used for error messages.
     *
     * @param array $token Token to name.
     * @return string Nice name for the token.
     */
    public static function tokenName ($token) {
        switch($token[0]) {
            case 'eof': return '<eof>';
            case 'name': return 'name '.$token[1];
            case 'var': return 'variable $'.$token[1];
            case 'data':
                if (is_string($token[1]))
                    return 'string "'.addslashes($token[1]).'"';
                elseif (is_float($token[1]))
                    return 'float '.$token[1];
                elseif (is_int($token[1]))
                    return 'integer '.$token[1];
                else
                    return gettype($token[1]);
            case 'term': return $token[1];
            default: return $token[0];
        }
    }

    /**
     * Converts backslash escape sequences in strings to the proper value.
     * Only supports double-backslash and backslash-n (newline) currently.
     *
     * @param string $string String to decode.
     * @return string Decoded string.
     */
    public static function decodeSlashes ($string) {
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
     * @return mixed Array of subexpression matches on successful, or false if no match.
     */
    private function getRegex ($regex) {
        if (!preg_match($regex, $this->src, $ar, 0, $this->pos))
            return false;
        $this->pos += strlen($ar[0]);
        $this->line += substr_count($ar[0], "\n");
        return $ar;
    }

    /**
     * Retrieves the next token in the input stream.
     *
     * @return array Next token.
     */
    private function next () {
        // EOF
        if ($this->pos >= strlen($this->src))
            return array('eof', null);

        // outside of a command?
        if (!$this->inCmd) {
            // find next opening delimiter
            $next = strpos($this->src, $this->delimStart, $this->pos);

            // set $next to last byte
            if ($next === FALSE)
                $next = strlen($this->src);

            // just a literal?
            if ($next > $this->pos) {
                $text = substr($this->src, $this->pos, $next - $this->pos);
                $this->tokline = $this->line;
                $this->line += substr_count($this->src, "\n", $this->pos, $next - $this->pos);
                $this->pos = $next;
                return array('literal', $text);
            }

            // setup inside command
            $this->inCmd = true;
            $this->pos = $next + 2;
        }

        // skip spaces and comments
        while (($ar = $this->getRegex('/(?:\s+|(?:\/\*.*?\*\/|\/\/.*?($|'.preg_quote($this->delimEnd).')))/msA')) !== false) {
            // line comment ended with an end delimiter
            if (isset($ar[1]) && $ar[1] == $this->delimEnd) {
                $this->inCmd = false;
                return array('term', $this->delimEnd);
            }
        }

        // get next token
        $this->tokline = $this->line;
        if (($token = $this->getRegex('/(?:'.preg_quote($this->delimEnd).'|\$\w+|\d+(?:[.]\d+)?|\w+|==|!=|!in\b|<=|>=|\|\||&&|->|\.\.|.)/msA')) === false)
            throw new SugarParseException($this->file, $this->line, 'garbage at: '.substr($this->src, $this->pos, 12));
        $token = $token[0];

        // if at end, mark that
        if ($token == $this->delimEnd)
            $this->inCmd = false;

        // string
        if ($token == '"') {
            if (($string = $this->getRegex('/((?:[^"\\\\]*\\\\.)*[^"]*)"/msA')) === false)
                throw new SugarParseException($this->file, $this->line, 'unterminated string constant at: '.substr($this->src, $this->pos, 12));
            return array('data', SugarTokenizer::decodeSlashes($string[1]));
        } elseif ($token == '\'') {
            if (($string = $this->getRegex('/((?:[^\'\\\\]*\\\\.)*[^\']*)\'/msA')) === false)
                throw new SugarParseException($this->file, $this->line, 'unterminated string constant at: '.substr($this->src, $this->pos, 12));
            return array('data', SugarTokenizer::decodeSlashes($string[1]));
        }

        // variable
        if (strlen($token) > 1 && $token[0] == '$') 
            return array('var', substr($token, 1));
        // terminator
        elseif ($token == $this->delimEnd || $token == ';')
            return array('term', $token);
        // keyword or special symbol
        elseif (in_array($token, array('if', 'elif', 'else', 'end', 'foreach', 'in', 'loop', 'while', 'nocache')))
            return array($token, null);
        // integer
        elseif (preg_match('/^\d+$/', $token))
            return array('data', intval($token));
        // float
        elseif (preg_match('/^\d+[.]\d+$/', $token))
            return array('data', floatval($token));
        // true and false
        elseif ($token == 'true')
            return array('data', true);
        elseif ($token == 'false')
            return array('data', false);
        // name
        elseif (preg_match('/^\w+$/', $token))
            return array('name', $token);
        // generic operator
        else
            return array($token, null);
    }

    /**
     * Checks to the see if the next token matches the requested token
     * type.  If it does, the token is consumed.  The token data is
     * stored in the second parameter.  True is returned if the token
     * was consumed, and false otherwise.
     *
     * @param string $accept Which token type to accept.
     * @param mixed $data Token token.
     * @return bool True if the token matched.
     */
    public function accept ($accept, &$data = null) {
        // return false if it's the wrong token
        if ($this->token[0] != $accept)
            return false;

        // store data
        $data = $this->token[1];

        // get next token
        $this->token = $this->next();
        return true;
    }

    /**
     * Checks to see if the next token is one of a list of given types.
     *
     * @param array $accept Tokens to accept.
     * @return bool True if one of the given token types matches.
     */
    public function peekAny (array $accept) {
        return in_array($this->token[0], $accept);
    }

    /**
     * Checks to the see if the next token matches the requested token
     * type.  If it does, the token is consumed.  The token data is
     * stored in the second parameter.  If the token does not match,
     * a {@link SugarParseException} is raised.
     *
     * @param string $accept Which token type to accept.
     * @param mixed $data Token token.
     */
    public function expect ($expect, &$data = null) {
        // throw an error if it's the wrong token
        if ($this->token[0] != $expect)
            throw new SugarParseException($this->file, $this->tokline, 'expected '.$expect.'; found '.SugarTokenizer::tokenName($this->token));

        // store value
        $data = $this->token[1];

        // get next token
        $this->token = $this->next();
    }

    /**
     * Similar to {@link SugarTokenizer::expect}, except that it checks for
     * any of standard Sugar operators, and the matched operator (if any)
     * is returned.
     *
     * @return mixed The operator if one matches, or false otherwise.
     */
    public function getOp () {
        $op = $this->token[0];

        // convert = to == for operators
        if ($op == '=')
            $op = '==';

        // if it's a valid operator, return it
        if (isset(SugarParser::$precedence[$op])) {
            // get next token
            $this->token = $this->next();

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
    public function getLine () {
        return $this->line;
    }

    /**
     * Returns the file name given to the constructor.
     *
     * @return string File name.
     */
    public function getFile () {
        return $this->file;
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
