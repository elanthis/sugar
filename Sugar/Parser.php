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

require_once SUGAR_ROOTDIR.'/Sugar/Tokenizer.php';
require_once SUGAR_ROOTDIR.'/Sugar/Runtime.php';

class SugarParser {
    private $tokens = null;
    private $output = array();
    private $stack = array();
    private $block;
    private $sugar;

    static public $precedence = array(
        '.' => 0, '->' => 0, 'method' => 0, '[' => 0,
        '!' => 1, 'negate' => 1,
        '*' => 2, '/' => 2, '%' => 2,
        '+' => 3, '-' => 3,
        '..' => 4,
        '==' => 5, '<' => 5, '>' => 5,
        '!=' => 5, '<=' => 5, '>=' => 5, 'in' => 5, '!in' => 5,
        '||' => 6, '&&' => 6,
        '(' => 100 // safe wrapper
    );

    public function __construct (&$sugar) {
        $this->sugar =& $sugar;
    }

    private function parseFunctionArgs ($parens) {
        // if parents is true, then the args are
        // parenthesized, and require commas and a
        // trailing comma

        $params = array();
        $first = true;
        while (!$this->tokens->accept($parens?')':'term')) {
            // after the first arg, require a separating comma
            if ($first)
                $first = false;
            elseif ($parens)
                $this->tokens->expect(',');

            // check for name= assignment
            if ($this->tokens->accept('name', $name)) {
                // if followed by a (, then it's actually a function call
                if ($this->tokens->accept('(')) {
                    $nparams = $this->parseFunctionArgs(true);
                    $this->output []= array('call', $name, $nparams, $this->tokens->getFile(), $this->tokens->getLine());
                    $params []= $this->compileExpr(true);
                // otherwise, we expect a name= construct
                } else {
                    $this->tokens->expect('=');
                    $params [$name]= $this->compileExpr();
                }
            // regular parameter
            } else {
                $params []= $this->compileExpr();
            }
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
            // pop higher precedence operators
            $this->collapseOps(SugarParser::$precedence[$op]);

            // push op
            $this->stack []= $op;

            // if it's an array . op, we can take a name
            if ($op == '.' && $this->tokens->accept('name', $name)) {
                $this->output []= array('push', $name);

            // if it's an object -> op, we can also take a name
            } elseif ($op == '->' && $this->tokens->accept('name', $name)) {
                // check if this is a method call
                if ($this->tokens->accept('(')) {
                    $method = $name;
                    $params = $this->parseFunctionArgs(true);

                    // remove -> operator, create method call
                    array_pop($this->stack);
                    $this->output []= array_merge(array_pop($this->output), array('method', $method, $params, $this->tokens->getFile(), $this->tokens->getLine()));

                // not a method call
                } else {
                    $this->output []= array('push', $name);
                }

            // if it's an array [] operator, we need to handle the trailing ]
            } elseif ($op == '[') {
                // replace [ with .
                array_pop($this->stack);
                $this->stack []= '.';

                // compile rest of expression
                $this->compileTerminal();
                $this->tokens->expect(']');

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
        } elseif ($this->tokens->accept('name', $name)) {
            // if it's not followed by a (, its not a function call
            $this->tokens->expect('(');

            $params = $this->parseFunctionArgs(true);

            // return new function all
            $this->output []= array('call', $name, $params, $this->tokens->getFile(), $this->tokens->getLine());

        // static values
        } elseif ($this->tokens->accept('data', $data)) {
            $this->output []= array('push', $data);

        // vars
        } elseif ($this->tokens->accept('var', $name)) {
            $this->output []= array('lookup', $name);

        // error
        } else {
            // HACK: value is not a real type
            $this->tokens->expect('value');
        }
    }

    // compile a block of code
    public function compileBlock () {
        $block = array();

        // build byte-code
        while (!$this->tokens->peekAny(array('eof', 'else', 'elif', 'end'))) {
            // raw string
            if ($this->tokens->accept('literal', $literal)) {
                $block []= array('echo', $literal);

            // if the command is empty, ignore
            } elseif ($this->tokens->accept('term')) {
                // do nothing

            // print raw value
            } elseif ($this->tokens->accept('if')) {
                // get first clause expr and body
                $ops = $this->compileExpr();
                $this->tokens->expect('term');
                $body = $this->compileBlock();
                $clauses = array(array($ops, $body));

                // get elif clauses
                while ($this->tokens->accept('elif')) {
                    $ops = $this->compileExpr();
                    $this->tokens->expect('term');
                    $body = $this->compileBlock();
                    $clauses []= array($ops, $body);
                }

                // optional else clause
                if ($this->tokens->accept('else')) {
                    $body = $this->compileBlock();
                    $clauses []= array(false, $body);
                }

                $this->tokens->expect('end');
                $this->tokens->expect('term');

                // push block
                $block []= array('if', $clauses);

            // while loop
            } elseif ($this->tokens->accept('while')) {
                // get expression
                $test = $this->compileExpr();
                $this->tokens->expect('term');

                // get body
                $body = $this->compileBlock();
                $this->tokens->expect('end');
                $this->tokens->expect('term');

                // push block
                $block []= array('while', $test, $body);

            // range loop
            } elseif ($this->tokens->accept('loop')) {
                // name in lower,upper
                $this->tokens->expect('var', $name);
                $this->tokens->expect('in');
                $lower = $this->compileExpr();
                $this->tokens->expect(',');
                $upper = $this->compileExpr();

                // optional: ,step
                if ($this->tokens->accept(','))
                    $step = $this->compileExpr();
                else
                    $step = array('push', 1);

                $this->tokens->expect('term');

                // block
                $body = $this->compileBlock();
                $this->tokens->expect('end');
                $this->tokens->expect('term');

                // push block
                $block []= $lower;
                $block []= $upper;
                $block []= $step;
                $block []= array('range', $name, $body);

            // loop over an array
            } elseif ($this->tokens->accept('foreach')) {
                $key = null;
                $name = null;

                // get name
                $this->tokens->expect('var', $name);

                // is it a key,name pair?
                if ($this->tokens->accept(',')) {
                    $key = $name;
                    $this->tokens->expect('var', $name);
                }

                // now we need the expression
                $this->tokens->expect('in');
                $ops = $this->compileExpr();
                $this->tokens->expect('term');

                // and the block itself
                $body = $this->compileBlock();
                $this->tokens->expect('end');
                $this->tokens->expect('term');

                // store foreach block
                $block []= $ops;
                $block []= array('foreach', $key, $name, $body);

            // inhibit cahing
            } elseif ($this->tokens->accept('nocache')) {
                // get block
                $body = $this->compileBlock();
                $this->tokens->expect('end');

                $block []= array('nocache', $body);

            // if we have a var, we might have an assignment... or just an expression
            } elseif ($this->tokens->accept('var', $name)) {
                // if it's followed by a =, it's an assignment
                if ($this->tokens->accept('=')) {
                    $ops = $this->compileExpr();
                    $this->tokens->expect('term');

                    $block []= $ops;
                    $block []= array('assign', strtolower($name));

                // otherwise, it's an expression
                } else {
                    // push the variable request, compile expr skipping first term
                    // DIRTY HACK
                    $this->output []= array('lookup', $name);
                    $ops = $this->compileExpr(true);
                    $this->tokens->expect('term');

                    $block []= $ops;
                    $block []= array('print');
                }

            // function call?
            } elseif ($this->tokens->accept('name', $func)) {
                // lookup function
                $invoke = $this->sugar->getFunction($func);
                if (!$invoke)
                    throw new SugarParseException($this->tokens->getFile(), $this->tokens->getLine(), 'unknown function `'.$func.'`');

                // check if we're parenthesized, and get args
                if ($this->tokens->accept('('))
                    $params = $this->parseFunctionArgs(true);
                else
                    $params = $this->parseFunctionArgs(false);

                // build function call
                $block []= array('call', $func, $params, $this->tokens->getFile(), $this->tokens->getLine());

                // if the function does not have SUPPRESS_RETURN, print return val
                if ( !($invoke[2] & SUGAR_FUNC_SUPPRESS_RETURN))
                    $block []= array('print');

            // we have a statement
            } else {
                $ops = $this->compileExpr();
                $this->tokens->expect('term');

                if (SugarParser::isData($ops)) {
                    $block []= array('echo', $this->sugar->escape(SugarRuntime::showValue($ops[1])));
                } else {
                    $block []= $ops;
                    $block []= array('print');
                }
            }
        }

        // merge the block together
        $block = call_user_func_array('array_merge', $block);

        return $block;
    }

    // compile the given source code into bytecode
    public function compile ($src, $file = '<input>') {
        // create tokenizer
        $this->tokens = new SugarTokenizer($src, $file);

        // build byte-code
        $bytecode = $this->compileBlock();
        $this->tokens->expect('eof');

        // free tokenizer
        $this->tokens = null;

        // create meta-block
        $code = array('type' => 'ctpl', 'version' => SUGAR_VERSION, 'bytecode' => $bytecode);
        return $code;
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
