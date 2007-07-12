<?php
class SugarParser {
    private $tokens = null;
    private $output = array();
    private $stack = array();
    private $blocks = array();
    private $sugar;

    static $precedence = array(
        '.' => 0, '->' => 0,
        '!' => 1, 'negate' => 1,
        '*' => 2, '/' => 2, '%' => 2,
        '+' => 3, '-' => 3,
        '..' => 4,
        '==' => 5, '=' => 5, '<' => 5, '>' => 5,
        '!=' => 5, '<=' => 5, '>=' => 5, 'in' => 5,
        '||' => 6, '&&' => 6,
        '(' => 11, '[' => 11
    );

    public function __construct (&$sugar) {
        $this->sugar =& $sugar;
    }

    private function collapseOps ($level) {
        while ($this->stack && SugarParser::$precedence[$this->stack[count($this->stack)-1]] <= $level) {
            // get operator
            $op = array_pop($this->stack);

            // if unary, pop right-hand operand
            if ($op == '!' || $op == 'negate') {
                $right = array_pop($this->output);

                // optimize away if operand is data
                if (SugarParser::isData($right))
                    $this->output []= array('push', SugarRuntime::execute($this->sugar, array_merge($right, array($op))));
                // can't optimize away - emit opcodes
                else
                    $this->output []= array_merge($right, array($op));

            // binary, pop both
            } else {
                $right = array_pop($this->output);
                $left = array_pop($this->output);

                // create method call
                if ($op == '->' && $right[0] == 'call')
                    $this->output []= array_merge($left, array('method', $right[1], $right[2]));
                // optimize away if both operands are constant data
                elseif (SugarParser::isData($left) && SugarParser::isData($right))
                    $this->output []= array('push', SugarRuntime::execute($this->sugar, array_merge($left, $right, array($op))));
                // can't optimize away - emit opcodes
                else
                    $this->output []= array_merge($left, $right, array($op));
            }
        }
    }

    private static function isData (&$node) {
        return (count($node) == 2 && $node[0] == 'push');
    }

    private function E () {
        // expect one
        $this->P();

        // while we have a binary operator, continue chunking along
        while (($op = $this->tokens->peek()) && array_key_exists($op[0], SugarParser::$precedence)) {
            $this->B();
            $this->P();
        }

        // pop remaining operators
        $this->collapseOps(10);
    }

    private function P () {
        $t = $this->tokens->peek();

        // unary operator
        if ($t[0] == '-' || $t[0] == '!') {
            $this->U();
            return;

        // array constructor
        } elseif ($t[0] == '[') {
            // consume
            $this->tokens->pop();

            // push ( to mark array constructor
            $this->stack []= '[';

            // read in elements
            $elems = array();
            $data = true;
            $end = $this->tokens->peek();
            while ($end[0] != ']') {
                // read in element
                $elem = $this->compileExpr();
                $elems []= $elem;

                // if not pure data, unmark data flag
                if ($data && !$this->isData($elem))
                    $data = $false;

                // consume comma
                $end = $this->tokens->get();
                if ($end[0] != ',' && $end[0] != ']')
                    throw new SugarParseException($end[2], $end[3], 'unexpected '.SugarTokenizer::tokenName($end).'; expected , or ]');
            }
            $this->tokens->pop();

            // if the data flag is true, all elements are pure data,
            // so we can push this as a value instead of an opcode
            if ($data) {
                foreach ($elems as $i=>$v)
                    $elems[$i] = $v[1];
                $this->output []= array('push', $elems);
            } else {
                $this->output []= array('array', $elems);
            }

            // pop [
            array_pop($this->stack);

        // sub-expression
        } elseif ($t[0] == '(') {
            // consume paren
            $this->tokens->pop();

            // push ( to mark sub-expression
            $this->stack []= '(';

            // compile sub-expression
            $this->output []= $this->compileExpr();

            // pop (
            array_pop($this->stack);

            // ensure trailing )
            $end = $this->tokens->get();
            if ($end[0] != ')')
                throw new SugarParseException($end[2], $end[3], 'unexpected '.SugarTokenizer::tokenName($end).'; expected )');

        // function call OR static name
        } elseif ($t[0] == 'name') {
            // store name
            $name = $t[1];
            $this->tokens->pop();

            // if it's not followed by a (, its not a function call
            $t = $this->tokens->peek();
            if ($t[0] != '(') {
                $this->output []= array('push', $name);
                return;
            }
            $this->tokens->pop();

            // read args
            $params = array();
            $token = $this->tokens->peek();
            $this->stack []= '(';
            while ($token[0] != ')') {
                // check for name= assignment
                $check = $this->tokens->peek(1);
                if ($token[0] == 'name' && $check[0] == '=')
                    $params [$token[1]]= $this->compileExpr();
                // regular parameter
                else
                    $params []= $this->compileExpr();

                // check trailing token
                $token = $this->tokens->peek();
                if ($token[0] != ',' && $token[0] != ')')
                    throw new SugarParseException($token[2], $token[3], 'unexpected '.SugarTokenizer::tokenName($token).'; expected ) or ,');
                // pop the ,
                elseif ($token[0] == ',')
                    $this->tokens->pop();
            }
            $this->tokens->pop();
            array_pop($this->stack);

            // return new function all
            $this->output []= array('call', $name, $params);

        // ints
        } elseif ($t[0] == 'data') {
            $this->output []= array('push', $t[1]);
            $this->tokens->pop();

        // vars
        } elseif ($t[0] == 'var') {
            $this->output []= array('lookup', $t[1]);
            $this->tokens->pop();

        // error
        } else
            throw new SugarParseException($t[2], $t[3], 'unexpected '.SugarTokenizer::tokenName($t).'; expected value');
    }

    private function B () {
        $op = $this->tokens->get();
        $op = $op[0];

        // pop higher precedence operators
        $this->collapseOps(SugarParser::$precedence[$op]);

        // convert = to ===
        if ($op == '=') $op = '==';

        // push op
        $this->stack []= $op;
    }

    private function U () {
        $op = $this->tokens->get();
        $op = $op[0];

        // push correct unary operator
        if ($op == '-')
            $this->stack []= 'negate';
        elseif ($op == '!')
            $this->stack []= '!';

        // need another P
        $this->P();
    }

    private function compileExpr () {
        $this->E();
        return array_pop($this->output);
    }

    private function isExprNext () {
        $token = $this->tokens->peek();
        return in_array($token[0], array('(', '[', '-', '!', 'name', 'var', 'data'));
    }

    private function appendEcho ($text) {
        $block =& $this->blocks[count($this->blocks)-1];

        // if block ends in an echo, concat them; otherwise, add op
        if ($block[1][count($block[1])-2] == 'echo')
            $block[1][count($block[1])-1] .= $text;
        // otherwise, just append the ops
        else
            array_push($block[1], 'echo', $text);
    }

    // compile the given source code into bytecode
    public function compile ($src, $file = '<input>') {
        $this->blocks = array(
            array('main', array())
        );

        $this->tokens = new SugarTokenizer($src, $file);

        // build byte-code
        while (!$this->tokens->eof()) {
            $block =& $this->blocks[count($this->blocks)-1];

            // peek at token
            $token = $this->tokens->peek();

            // eof
            if (!$token) {
                break;

            // raw string
            } elseif ($token[0] == 'literal') {
                $this->tokens->pop();
                $this->appendEcho($token[1]);
                continue;

            // if the command is empty, ignore
            } elseif ($token[0] == '%>' || $token[0] == ';') {
                // do nothing

            // print raw value
            } elseif ($token[0] == 'if') {
                $this->tokens->pop();

                $ops = $this->compileExpr($this->tokens);

                $this->blocks []= array('if', array(), array(array($ops, null)));

            // else for if
            } elseif ($token[0] == 'else' || $token[0] == 'elif') {
                $this->tokens->pop();

                // get top block; must be an if or elif
                if ($block[0] != 'if' && $block[0] != 'elif')
                    throw new SugarParseException($block[2], $block[3], 'else missing if');

                // update block
                $block[0] = $token[0];
                $block[2][count($block[2])-1][1] = $block[1];
                $block[1] = array();
                $block[2] []= array(null, null);

                // elif test
                if ($token[0] == 'elif')
                    $block[2][count($block[2])-1][0] = $this->compileExpr($this->tokens);

            // range loop
            } elseif ($token[0] == 'loop') {
                $this->tokens->pop(1);

                // lead with name
                $name = $this->tokens->get();
                if ($name[0] != 'var')
                    throw new SugarParseException($name[2], $name[3], 'unexpected '.SugarTokenizer::tokenName($name).'; expected variable');

                // require in keyword
                $in = $this->tokens->get();
                if ($in[0] != 'in')
                    throw new SugarParseException($name[2], $name[3], 'unexpected '.SugarTokenizer::tokenName($in).'; expected in');

                // parse lower-bound
                $lower = $this->compileExpr($this->tokens);

                // expect .. keyword
                $range = $this->tokens->get();
                if ($range[0] != ',')
                    throw new SugarParseException($name[2], $name[3], 'unexpected '.SugarTokenizer::tokenName($range).'; expected ,');

                // parse upper bound
                $upper = $this->compileExpr($this->tokens);

                // parse optional step
                $range = $this->tokens->peek();
                if ($range[0] == ',') {
                    $this->tokens->pop();
                    $step = $this->compileExpr($this->tokens);
                } else {
                    $step = array('push', 1);
                }

                // push block
                $this->blocks []= array('loop', array(), $name[1], $lower, $upper, $step);

            // loop over an array
            } elseif ($token[0] == 'foreach') {
                $name = $this->tokens->peek(1);
                $sep = $this->tokens->peek(2);
                $name2 = $this->tokens->peek(3);
                $eq = $this->tokens->peek(4);

                // lead with name
                if ($name[0] != 'var')
                    throw new SugarParseException($name[2], $name[3], 'unexpected '.SugarTokenizer::tokenName($name).'; expected variable');

                // var = expression?
                if ($sep[0] == 'in') {
                    $key = null;
                    $name = $name[1];
                    $this->tokens->pop(3);

                // var , var = expression ?
                } elseif ($sep[0] == ',') {
                    // need a second name
                    if ($name2[0] != 'var')
                        throw new SugarParseException($name2[2], $name2[3], 'unexpected '.SugarTokenizer::tokenName($name2).'; expected variable');

                    // and follow with an =
                    if ($eq[0] != 'in')
                        throw new SugarParseException($eq[2], $eq[3], 'unexpected '.SugarTokenizer::tokenName($eq).'; expected in');
                
                    $key = $name[1];
                    $name = $name2[1];
                    $this->tokens->pop(5);

                // invalid
                } else {
                    throw new SugarParseException($sep[2], $sep[3], 'unexpected '.SugarTokenizer::tokenName($sep).'; expected , or in');
                }

                // compile expression
                $ops = $this->compileExpr($this->tokens);

                // store foreach block
                $this->blocks []= array('foreach', array(), $key, $name, $ops);

            // pop the block
            } elseif ($token[0] == 'end') {
                $this->tokens->pop();

                // can't end if we're in the main block
                if ($block[0] == 'main')
                    throw new SugarParseException($token[2], $token[3], 'end without an if or loop');

                // new top block
                array_pop($this->blocks);

                // compile
                switch ($block[0]) {
                    case 'loop':
                        $bc = array_merge($block[3], $block[4], $block[5], array('range', strtolower($block[2]), $block[1]));
                        break;
                    case 'foreach':
                        $bc = array_merge($block[4], array('foreach', strtolower($block[2]), strtolower($block[3]), $block[1]));
                        break;
                    case 'if':
                    case 'elif':
                    case 'else':
                        // store current block opcodes into last block
                        $block[2][count($block[2])-1][1] = $block[1];

                        // build if tree
                        $bc = array();
                        while (!empty($block[2])) {
                            $chunk = array_pop($block[2]);
                            if ($chunk[0])
                                $bc = array_merge($chunk[0], array('if', $chunk[1], $bc));
                            else
                                $bc = $chunk[1];
                        }

                        break;
                    default:
                        die('Internal Error: '.__FILE__.','.__LINE__);
                }

                // merge bytecode to top block
                $block =& $this->blocks[count($this->blocks)-1];
                $block[1] = array_merge($block[1], $bc);

            // if we have a var then a =, we have an assignment
            } elseif ($token[0] == 'var' && ($t2 = $this->tokens->peek(1)) && $t2[0] == '=') {
                // remember name value
                $name = $token[1];
            
                // remove tokens, parse
                $this->tokens->pop(2);
                $ops = $this->compileExpr($this->tokens);

                $block[1] = array_merge($block[1], $ops, array('assign', strtolower($name)));

            // function call?
            } elseif ($token[0] == 'name') {
                // remember name value
                $func = $token[1];
                $this->tokens->pop();

                // lookup function
                $invoke = $this->sugar->getFunction($func);
                if (!$invoke)
                    throw new SugarParseException($token[2], $token[3], 'unknown function: '.$func);

                // parse out parameters
                $params = array();
                $token = $this->tokens->peek();
                while ($token[0] != '%>' && $token[0] != ';') {
                    // check for name= syntax
                    $check = $this->tokens->peek(1);
                    if ($token[0] == 'name' && $check[0] == '=') {
                        $this->tokens->pop(2);
                        $params [$token[1]]= $this->compileExpr($this->tokens);

                    // regular parameter
                    } else {
                        $params []= $this->compileExpr($this->tokens);
                    }

                    // pop optional ,
                    $token = $this->tokens->peek();
                    if ($token[0] == ',')
                        $this->tokens->pop();
                }

                // build function call
                array_push($block[1], 'call', $func, $params);

                // if the function does not have SUPPRESS_RETURN, print return val
                if ( !($invoke[2] & SUGAR_FUNC_SUPPRESS_RETURN))
                    $block[1] []= 'print';

            // we have a statement
            } else {
                $ops = $this->compileExpr($this->tokens);

                if (SugarParser::isData($ops))
                    $this->appendEcho(SugarRuntime::showValue($ops[1]));
                else
                    $block[1] = array_merge($block[1], $ops, array('print'));
            }

            // we should have the end token now
            $end = $this->tokens->get();
            if ($end[0] != '%>' && $end[0] != ';')
                throw new SugarParseException($end[2], $end[3], 'unexpected '.SugarTokenizer::tokenName($end).'; expected %>');
        }

        // still in a block?
        if (count($this->blocks) != 1)
            throw new SugarParseException($end[2], $end[3], 'unxpected end of file; expected end');

        // free tokenizer
        $this->tokens = null;

        return $this->blocks[0][1];
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
