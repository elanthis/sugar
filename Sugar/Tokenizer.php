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
            case 'string': return 'string "'.addslashes($token[1]).'"';
            case 'int': return 'integer '.($token[1]);
            case 'float': return 'number '.($token[1]);
            case 'term': return $token[1];
            default: return $token[0];
        }
    }

    // get next token
    private function next () {
        static $pattern = '/(\s*)(%>|\$\w+|\d+(?:[.]\d+)?|\w+|"((?:[^"\\\\]*\\\\.)*[^"]*)"|\'((?:[^\'\\\\]*\\\\.)*[^\']*)\'|\/\*.*?\*\/|\/\/.*?($|%>)|==|!=|<=|>=|\|\||&&|->|\.\.|.)/ms';

        // EOF
        if ($this->pos >= strlen($this->src)) {
            return array('eof', null, $this->file, $this->line);
        }

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
                $line = $this->line;
                $this->line += substr_count($this->src, "\n", $this->pos, $next - $this->pos);
                $this->pos = $next;
                return array('literal', $text, $this->file, $line);
            }

            // setup inside command
            $this->inCmd = true;
            $this->pos = $next + 2;
        }

        // keep looping until we get something valid - used mainly for comments
        while (true) {
            // get next token
            if (!preg_match($pattern, $this->src, $ar, 0, $this->pos))
                throw new SugarParseException($this->file, $this->line, 'garbage at: '.substr($this->src, $this->pos, 12));
            $this->pos += strlen($ar[0]);

            // calc line count
            $line = $this->line + substr_count($ar[1], "\n");
            $this->line = $line + substr_count($ar[2], "\n");

            // if at end, mark that
            if ($ar[2] == '%>')
                $this->inCmd = false;

            // comments
            if (substr($ar[2], 0, 2) == '/*' || substr($ar[2], 0, 2) == '//') {
                // if the comment ends in %> (only for // comments), return that
                if (substr($ar[2], -2, 2) == '%>') {
                    $this->inCmd = false;
                    return array('%>', null, $this->file, $line);
                }
                // otherwise, continue to next token
                continue;
            }

            // string
            if ($ar[3])
                return array('data', stripslashes($ar[3]), $this->file, $line);
            elseif ($ar[4])
                return array('data', stripslashes($ar[4]), $this->file, $line);
            // variable
            elseif (strlen($ar[2]) > 1 && $ar[2][0] == '$') 
                return array('var', substr($ar[2], 1), $this->file, $line);
            // terminator
            elseif ($ar[2] == '%>' || $ar[2] == ';')
                return array('term', $ar[2], $this->file, $line);
            // keyword or special symbol
            elseif (in_array($ar[2], array('if', 'elif', 'else', 'end', 'foreach', 'in', 'loop', 'while', 'nocache')))
                return array($ar[2], null, $this->file, $line);
            // integer
            elseif (preg_match('/^\d+$/', $ar[2]))
                return array('data', intval($ar[2]), $this->file, $line);
            // float
            elseif (preg_match('/^\d+[.]\d+$/', $ar[2]))
                return array('data', floatval($ar[2]), $this->file, $line);
            // name
            elseif (preg_match('/^\w+$/', $ar[2]))
                return array('name', $ar[2], $this->file, $line);
            // generic operator
            else
                return array($ar[2], null, $this->file, $line);
        }
    }

    // if the requested token is available, pop it and return true; else return false
    // store the token data in the optional second parameter, which should be passed
    // a reference
    public function accept ($accept, $data = null) {
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
    public function expect ($expect, $data = null) {
        // throw an error if it's the wrong token
        if ($this->token[0] != $expect)
            throw new SugarParseException($this->token[2], $this->token[3], 'unexpected '.SugarTokenizer::tokenName($this->token).'; expected '.$expect);

        // store value
        $data = $this->token[1];

        // get next token
        $this->token = $this->next();
    }

    // get an operator token
    public function getOp () {
        $op = $this->token[0];

        // convert == to = for operators
        if ($op == '==')
            $op = '=';

        // if it's a valid operator, return it
        if (isset(SugarParser::$precedence[ $op])) {
            // get next token
            $this->token = $this->next();

            return $op;
        } else {
            return false;
        }
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
