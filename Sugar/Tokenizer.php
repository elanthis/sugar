<?php
/****************************************************************************
PHP-Sugar
Copyright (c) 2007  AwesomePlay Productions, Inc. and
contributors.  All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

 * Redistributions of source code must retain the above copyright notice,
   this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in the
   documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
ARE DISCLAIMED. IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
DAMAGE.
****************************************************************************/

class SugarTokenizer {
    private $src;
    private $token = null;
    private $pos = 0;
    private $inCmd = false;
    private $file;
    private $line = 1;
    private $tokline;

    public function __construct ($src, $file = '<input>') {
        $this->src = $src;
        $this->file = $file;

        $this->token = $this->next();
    }

    // display a user-friendly name for a particular token
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

    // handle slashes in input strings
    public static function decodeSlashes ($string) {
        $string = str_replace('\\n', "\n", $string);
        $string = stripslashes($string);
        return $string;
    }

    // fetch a regular expression match set, updating pos and line counts
    private function getRegex ($regex) {
        if (!preg_match($regex, $this->src, $ar, 0, $this->pos))
            return false;
        $this->pos += strlen($ar[0]);
        $this->line += substr_count($ar[0], "\n");
        return $ar;
    }

    // get next token
    private function next () {
        // EOF
        if ($this->pos >= strlen($this->src))
            return array('eof', null);

        // outside of a command?
        if (!$this->inCmd) {
            // find next <%
            $next = strpos($this->src, '<%', $this->pos);

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
        while ($this->getRegex('/(\s+|(?:\/\*.*?\*\/|\/\/.*?($|%>)))/msA') !== false) {
            // line comment ended with a %>
            if ($ar[1] == '%>') {
                $this->inCmd = false;
                return array('term', '%>');
            }
        }

        // get next token
        $this->tokline = $this->line;
        if (($token = $this->getRegex('/(?:%>|\$\w+|\d+(?:[.]\d+)?|\w+|==|!=|<=|>=|\|\||&&|->|\.\.|.)/msA')) === false)
            throw new SugarParseException($this->file, $this->line, 'garbage at: '.substr($this->src, $this->pos, 12));
        $token = $token[0];

        // if at end, mark that
        if ($token == '%>')
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
        elseif ($token == '%>' || $token == ';')
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

    // if the requested token is available, pop it and return true; else return false
    // store the token data in the optional second parameter, which should be passed
    // a reference
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

    // ensures that the requested token is next, throws an error if it isn't.
    // store the token data in the optional second parameter, which should be passed
    // a reference
    public function expect ($expect, &$data = null) {
        // throw an error if it's the wrong token
        if ($this->token[0] != $expect)
            throw new SugarParseException($this->file, $this->tokline, 'expected '.$expect.'; found '.SugarTokenizer::tokenName($this->token));

        // store value
        $data = $this->token[1];

        // get next token
        $this->token = $this->next();
    }

    // get an operator token
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

    // return current line
    public function getLine () {
        return $this->line;
    }

    // return current file
    public function getFile () {
        return $this->file;
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
