<?php
class SugarTokenizer {
    private $src;
    private $tokens = array();
    private $pos = 0;
    private $inCmd = false;
    private $eof = false;
    private $file;
    private $line = 1;

    public function __construct ($src, $file = '<input>') {
        $this->src = $src;
        $this->file = $file;
    }

    // display a user-friendly name for a particular token
    public static function tokenName ($token) {
        if (!$token)
            return '<eof>';
        switch($token[0]) {
            case 'name': return 'name '.$token[1];
            case 'var': return 'variable $'.$token[1];
            case 'string': return 'string "'.addslashes($token[1]).'"';
            case 'int': return 'integer '.($token[1]);
            case 'float': return 'number '.($token[1]);
            default: return $token[0];
        }
    }

    // get next token
    private function getNext () {
        static $pattern = '/(\s*)(%>|\$\w+|\d+(?:[.]\d+)?|\w+|"((?:[^"\\\\]*\\\\.)*[^"]*)"|\'((?:[^\'\\\\]*\\\\.)*[^\']*)\'|\/\*.*?\*\/|\/\/.*?($|%>)|==|!=|<=|>=|\|\||&&|->|\.\.|.)/m';

        // EOF
        if ($this->pos >= strlen($this->src)) {
            $this->eof = true;
            return null;
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
            // keyword or special symbol
            elseif (in_array($ar[2], array('if', 'elif', 'else', 'end', 'foreach', 'in', 'loop')))
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

    // get next token
    public function get () {
        // got one pending?
        if ($this->tokens)
            return array_shift($this->tokens);

        // none pending; fetch
        return $this->getNext();
    }

    // peek at the next token
    public function peek ($i = 0) {
        // need to fill cache to $i+1 items
        while (count($this->tokens) <= $i) {
            $token = $this->getNext();
            if (!$token) // eof
                return null;
            $this->tokens []= $token;
        }

        // and return cache value
        return $this->tokens[$i];
    }

    // pop X tokens
    public function pop ($n = 1) {
        // clear cache up to $n items
        while ($n-- > 0 && !empty($this->tokens))
            array_shift($this->tokens);

        // consume more items
        while ($n-- > 0)
            $this->getNext();
    }

    // return true if at eof
    public function eof () {
        return $this->eof;
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
