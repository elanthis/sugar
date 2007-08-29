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

class SugarParser {
    private $tokens = null;
    private $output = array();
    private $stack = array();
    private $blocks = array();
    private $sugar;

    static public $precedence = array(
        '.' => 0, '->' => 0, 'method' => 0,
        '!' => 1, 'negate' => 1,
        '*' => 2, '/' => 2, '%' => 2,
        '+' => 3, '-' => 3,
        '..' => 4,
        '=' => 5, '<' => 5, '>' => 5,
        '!=' => 5, '<=' => 5, '>=' => 5, 'in' => 5,
        '||' => 6, '&&' => 6,
        '(' => 100 // safe wrapper
    );

    public function __construct (&$sugar) {
        $this->sugar =& $sugar;
    }

    private function parseFunctionArgs ($term) {
        // read args
        $params = array();
        while (!$this->tokens->accept($term)) {
            // check for name= assignment
            if ($this->tokens->accept('name', &$name)) {
                // if followed by a (, then it's actually a function call
                if ($this->tokens->accept('(')) {
                    $nparams = $this->parseFunctionArgs(')');
                    $params []= array('call', $name, $nparams);
                // otherwise, we expect a name= construct
                } else {
                    $this->tokens->expect('=');
                    $params [$name]= $this->compileExpr();
                }
            // regular parameter
            } else {
                $params []= $this->compileExpr();
            }

            // consume optional ,
            $this->tokens->accept(',');
        }
        return $params;
    }

    private function collapseOps ($level) {
        while ($this->stack && SugarParser::$precedence[end($this->stack)] <= $level) {
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

                // optimize away if both operands are constant data
                if (SugarParser::isData($left) && SugarParser::isData($right))
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

    private function compileExpr ($skip = false) {
        // wrap operator stack
        $this->stack []= '(';

        // if skip is true (only used for our hacky variable assignment
        // handling), don't do this part
        if (!$skip)
            $this->compileTerminal();

        // while we have a binary operator, continue chunking along
        while ($op = $this->tokens->getOp()) {
            // convert = to ==
            if ($op == '=')
                $op = '==';

            // pop higher precedence operators
            $this->collapseOps(SugarParser::$precedence[$op]);

            // push op
            $this->stack []= $op;

            // if it's an array . op, we can take a name
            if ($op == '.' && $this->tokens->accept('name', &$name)) {
                $this->output []= array('push', $name);

            // if it's an object -> op, we can also take a name
            } elseif ($op == '->' && $this->tokens->accept('name', &$name)) {
                // check if this is a method call
                if ($this->tokens->accept('(')) {
                    $method = $name;
                    $params = $this->parseFunctionArgs(')');

                    // remove -> operator, create method call
                    array_pop($this->stack);
                    $this->output []= array_merge(array_pop($this->output), array('method', $method, $params));

                // not a method call
                } else {
                    $this->output []= array('push', $name);
                }

            // regular case, just go
            } else {
                $this->compileTerminal();
            }
        }

        // pop remaining operators
        $this->collapseOps(10);

        // peel operator stack
        array_pop($this->stack);

        // return output
        return array_pop($this->output);
    }

    private function compileTerminal () {
        // unary -
        if ($this->tokens->accept('-')) {
            $this->stack []= 'negate';
            $this->compileTerminal();
            return;

        // unary !
        } elseif ($this->tokens->accept('!')) {
            $this->stack []= '!';
            $this->compileTerminal();
            return;

        // array constructor
        } elseif ($this->tokens->accept('[')) {
            // read in elements
            $elems = array();
            $data = true;
            while (!$this->tokens->accept(']')) {
                // read in element
                $elem = $this->compileExpr();
                $elems []= $elem;

                // if not pure data, unmark data flag
                if ($data && !$this->isData($elem))
                    $data = $false;

                // if we have a ], end
                if ($this->tokens->accept(']'))
                    break;

                // require a comma before next item
                $this->tokens->expect(',');
            }

            // if the data flag is true, all elements are pure data,
            // so we can push this as a value instead of an opcode
            if ($data) {
                foreach ($elems as $i=>$v)
                    $elems[$i] = $v[1];
                $this->output []= array('push', $elems);
            } else {
                $this->output []= array('array', $elems);
            }

        // sub-expression
        } elseif ($this->tokens->accept('(')) {
            // compile sub-expression
            $this->output []= $this->compileExpr();

            // ensure trailing )
            $this->tokens->expect(')');

        // function call
        } elseif ($this->tokens->accept('name', &$name)) {
            // if it's not followed by a (, its not a function call
            $this->tokens->expect('(');

            $params = $this->parseFunctionArgs(')');

            // return new function all
            $this->output []= array('call', $name, $params);

        // static values
        } elseif ($this->tokens->accept('data', &$data)) {
            $this->output []= array('push', $data);

        // vars
        } elseif ($this->tokens->accept('var', &$name)) {
            $this->output []= array('lookup', $name);

        // error
        } else {
            // HACK: value is not a real type
            $this->tokens->expect('value');
        }
    }

    private function pushLiteral ($text) {
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
        while (!$this->tokens->accept('eof')) {
            $block =& $this->blocks[count($this->blocks)-1];

            // raw string
            if ($this->tokens->accept('literal', &$literal)) {
                $this->pushLiteral($literal);
                continue;

            // if the command is empty, ignore
            } elseif ($this->tokens->accept('term')) {
                continue;

            // print raw value
            } elseif ($this->tokens->accept('if')) {
                // get test
                $ops = $this->compileExpr();

                // push block
                $this->blocks []= array('if', array(), array(array($ops, null)));

            // else for if
            } elseif ($this->tokens->accept('else')) {
                // get top block; must be an if or elif
                if ($block[0] != 'if' && $block[0] != 'elif')
                    throw new SugarParseException($token[2], $token[3], 'else missing if');

                // update block
                $block[0] = 'else';
                $block[2][count($block[2])-1][1] = $block[1];
                $block[1] = array();
                $block[2] []= array(null, null);

            // elseif for if
            } elseif ($this->tokens->accept('elif')) {
                // get top block; must be an if or elif
                if ($block[0] != 'if' && $block[0] != 'elif')
                    throw new SugarParseException($token[2], $token[3], 'elif missing if');

                // test
                $ops = $this->compileExpr();

                // update block
                $block[0] = 'elif';
                $block[2][count($block[2])-1][1] = $block[1];
                $block[1] = array();
                $block[2] []= array($ops, null);

            // while loop
            } elseif ($this->tokens->accept('while')) {
                // get expression
                $test = $this->compileExpr();

                // push block
                $this->blocks []= array('while', array(), $test);

            // range loop
            } elseif ($this->tokens->accept('loop')) {
                // name in lower,upper
                $this->tokens->expect('var', &$name);
                $this->tokens->expect('in');
                $lower = $this->compileExpr();
                $this->tokens->expect(',');
                $upper = $this->compileExpr();

                // optional: ,step
                if ($this->tokens->accept(','))
                    $step = $this->compileExpr();
                else
                    $step = array('push', 1);

                // push block
                $this->blocks []= array('loop', array(), $name, $lower, $upper, $step);

            // loop over an array
            } elseif ($this->tokens->accept('foreach')) {
                $key = null;
                $name = null;

                // get name
                $this->tokens->expect('var', &$name);

                // is it a key,name pair?
                if ($this->tokens->accept(',')) {
                    $key = $name;
                    $this->tokens->expect('var', &$name);
                }

                // now we need the in
                $this->tokens->expect('in');

                // compile array expression
                $ops = $this->compileExpr();

                // store foreach block
                $this->blocks []= array('foreach', array(), $key, $name, $ops);

            // inhibit cahing
            } elseif ($this->tokens->accept('nocache')) {
                // store foreach block
                $this->blocks []= array('nocache', array());

            // pop the block
            } elseif ($this->tokens->accept('end')) {
                // can't end if we're in the main block
                if ($block[0] == 'main')
                    throw new SugarParseException($token[2], $token[3], 'end without an if or loop');

                // pop top block
                array_pop($this->blocks);

                // compile
                switch ($block[0]) {
                    case 'loop':
                        $bc = array_merge($block[3], $block[4], $block[5], array('range', strtolower($block[2]), $block[1]));
                        break;
                    case 'foreach':
                        $bc = array_merge($block[4], array('foreach', strtolower($block[2]), strtolower($block[3]), $block[1]));
                        break;
                    case 'while':
                        $bc = array('while', $block[2], $block[1]);
                        break;
                    case 'nocache':
                        $bc = array('nocache', $block[1]);
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

            // if we have a var, we might have an assignment... or just an expression
            } elseif ($this->tokens->accept('var', &$name)) {
                // if it's followed by a =, it's an assignment
                if ($this->tokens->accept('=')) {
                    $ops = $this->compileExpr();
                    $block[1] = array_merge($block[1], $ops, array('assign', strtolower($name)));

                // otherwise, it's an expression
                } else {
                    // push the variable request, compile expr skipping first term
                    // DIRTY HACK
                    $this->output []= array('lookup', $name);
                    $ops = $this->compileExpr(true);
                    $block[1] = array_merge($block[1], $ops, array('print'));
                }

            // function call?
            } elseif ($this->tokens->accept('name', &$func)) {
                // lookup function
                $invoke = $this->sugar->getFunction($func);
                if (!$invoke)
                    throw new SugarParseException($token[2], $token[3], 'unknown function: '.$func);

                // get args
                $params = $this->parseFunctionArgs('term');

                // build function call
                array_push($block[1], 'call', $func, $params);

                // if the function does not have SUPPRESS_RETURN, print return val
                if ( !($invoke[2] & SUGAR_FUNC_SUPPRESS_RETURN))
                    $block[1] []= 'print';

                // note that we already accepted the terminator token
                continue;

            // we have a statement
            } else {
                $ops = $this->compileExpr();

                if (SugarParser::isData($ops))
                    $this->pushLiteral($this->sugar->escape(SugarRuntime::showValue($ops[1])));
                else
                    $block[1] = array_merge($block[1], $ops, array('print'));
            }

            // we should have the end token now
            $this->tokens->expect('term');
        }

        // still in a block? throw an error
        if (count($this->blocks) != 1)
            $this->tokens->expect('end');

        // free tokenizer
        $this->tokens = null;

        return $this->blocks[0][1];
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
