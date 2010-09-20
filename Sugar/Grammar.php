<?php
/**
 * Template markup grammar parser.
 *
 * Defines the grammar parser engine used for Sugar markup.  This is a hand-
 * written recursive-descent parser.  It's simple in design, but very easy
 * to extend or modify.
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
 * @author     Shawn Pearce
 * @copyright  2008-2010 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    SVN: $Id$
 * @link       http://php-sugar.net
 * @access     private
 */

/**
 * Source tokenizer.
 */
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/Lexer.php';

/**#@+
 * Expression nodes.
 */
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/Node.php';
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/Node/Array.php';
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/Node/Call.php';
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/Node/Expr.php';
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/Node/Literal.php';
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/Node/Lookup.php';
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/Node/Pipe.php';
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/Node/Print.php';
/**#@-*/

/**
 * Runtime engine, used for optimization.
 */
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/Runtime.php';

/**
 * Template parser.
 *
 * This class implements the grammar parsing language for the Sugar
 * template language.
 *
 * @category   Template
 * @package    Sugar
 * @subpackage Compiler
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2008-2009 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    Release: 0.84
 * @link       http://php-sugar.net
 * @access     private
 */
final class Sugar_Grammar
{
    /**
     * Tokenizer.
     *
     * @var Sugar_Lexer
     */
    private $_tokens = null;

    /**
     * Sugar instance.
     *
     * @var Sugar
     */
    private $_sugar;

    /**
     * Block stack.
     *
     * @var array
     */
    private $_blocks = array();

    /**
     * Sections list.
     *
     * @var array
     */
    private $_sections = array();

    /**
     * Inherited template.
     *
     * @var string
     */
    private $_inherit = null;

    /**
     * Operator precedence map.
     *
     * @var array
     */
    static public $precedence = array(
        '.' => 1, '->' => 1, '[' => 1,
        '*' => 2, '/' => 2, '%' => 2,
        '+' => 3, '-' => 3,
        '..' => 4,
        '==' => 5, '<' => 5, '>' => 5,
        '!=' => 5, '<=' => 5, '>=' => 5, 'in' => 5, '!in' => 5,
        '||' => 6, '&&' => 6,
        '(' => 100 // safe wrapper
    );

    /**
     * Constructor.
     *
     * @param Sugar $sugar  Sugar instance.
     */
    public function __construct($sugar)
    {
        $this->_sugar = $sugar;
    }

    /**
     * Parsed out a list of function arguments.
     *
     * @return array Arguments.
     */
    private function _parseFunctionArgs()
    {
        $params = array();
        while (!$this->_tokens->peekAny(array(')', ']', '}', ',', Sugar_Token::TERMINATOR))) {
            // check for name= assignment
            $this->_tokens->expect(Sugar_Token::IDENTIFIER, $name);
            $this->_tokens->expect('=');

            // assign parameter
            $params [$name]= $this->_compileBinary();
        }
        return $params;
    }

    /**
     * Parsed out a list of built-in function arguments.
     *
     * Arguments to built-in functions may, currently, only be strings.
     *
     * The list of argument provided is checked against the list of
     * argumented entered by the user, and an error is thrown if
     * they do not match.
     *
     * @param string $name    Name of the built-in function, for errors.
     * @param array  $require List of required parameters, as name=>type
     *
     * @return array Arguments.
     *
     * @throws Sugar_Parse_Exception
     */
    private function _parseBuiltinFunctionArgs($name, array $require)
    {
        $params = array();

        // keep reading so long as we have identifiers (start of id=value)
        while ($this->_tokens->accept(Sugar_Token::IDENTIFIER, $id)) {
            // check for =value assignment
            $this->_tokens->expect('=');
            $this->_tokens->expect(Sugar_Token::LITERAL, $value);

            // ensure this parameter is allowed
            if (!isset($require[$id])) {
                throw new Sugar_Exception_Parse(
                    $this->_tokens->getFile(),
                    $this->_tokens->getLine(),
                    'unexpected parameter `'.$id.'` to built-in function '.$name
                );
            }

            // check type
            if ($require[$id] != gettype($value)) {
                throw new Sugar_Exception_Parse(
                    $this->_tokens->getFile(),
                    $this->_tokens->getLine(),
                    'wrong type for parameter `'.$id.'` to built-in function '.$name
                );
            }

            // assign parameter
            $params [$id]= $value;
        }

        // ensure all required parameters are set
        foreach ($require as $id=>$type) {
            if (!isset($params[$id])) {
                throw new Sugar_Exception_Parse(
                    $this->_tokens->getFile(),
                    $this->_tokens->getLine(),
                    'missing parameter `'.$id.'` to built-in function '.$name
                );
            }
        }
        
        return $params;
    }

    /**
     * Parsed out a list of method arguments.
     *
     * @return array Arguments.
     */
    private function _parseMethodArgs()
    {
        $params = array();
        while (!$this->_tokens->accept(')')) {
            // assign parameter
            $params []= $this->_compileBinary();

            // if we're at a ), end now
            if ($this->_tokens->accept(')')) {
                break;
            }

            // require a comma after every parameter
            $this->_tokens->expect(',');
        }
        return $params;
    }

    /**
     * Compile an entire expression.
     *
     * @return Sugar_Node Compiler node
     */
    private function _compileExpr() {
        // function call
        if ($this->_tokens->accept(Sugar_Token::IDENTIFIER, $name)) {
            // parse modifiers
            $modifiers = array();
            if ($this->_tokens->accept('|')) {
                $modifiers = $this->_compileModifiers();
            }

            // get args
            $params = $this->_parseFunctionArgs();

            // build function call
            $expr = new Sugar_Node_Call($this->_sugar);
            $expr->operator = 'call';
            $expr->name = $name;
            $expr->file = $this->_tokens->getFile();
            $expr->line = $this->_tokens->getLine();
            $expr->params = $params;

            // apply 
            if ($modifiers) {
                $pexpr = new Sugar_Node_Pipe($this->_sugar);
                $pexpr->node = $expr;
                $pexpr->modifiers = $modifiers;
                return $pexpr;
            } else {
                return $expr;
            }
        } else
        // other kind of expression
        {
            // compile a binary expression
            return $this->_compileBinary();
        }
    }

    /**
     * Collapses the output and operator stacks for all pending operators
     * under a given precedence level.
     *
     * @param int $level Precedence level to collapse under.
     *
     * @return bool True on success.
     */
    private function _collapseOps($level, &$stack, &$output)
    {
        while ($stack && self::$precedence[end($stack)] <= $level) {
            // get operator
            $op = array_pop($stack);

            // pop operands
            $right = array_pop($output);
            $left = array_pop($output);

            // create binary expression
            $expr = new Sugar_Node_Expr($this->_sugar);
            $expr->operator = $op;
            $expr->operands []= $left;
            $expr->operands []= $right;

            // push resulting expression
            $output []= $expr;
        }
        return true;
    }

    /**
     * Compile a binary expression.
     *
     * @return Sugar_Node Compiled node.
     */
    private function _compileBinary() {
        // create operator stack
        $stack = array();

        // first left-hand operand (possibly the only node)
        $output []= $this->_compileUnaryWithModifiers();

        // while we have a binary operator, continue chunking along
        while ($op = $this->_tokens->getOp()) {
            // pop higher precedence operators
            $this->_collapseOps(self::$precedence[$op], $stack, $output);

            // push operator and right-hand operand
            $stack []= $op;
            $output []= $this->_compileUnaryWithModifiers();
        }

        // pop remaining operators
        $this->_collapseOps(10, $stack, $output);

        // expect a single item in the output stack and no items in the
        // operator stack
        if (!empty($stack)) {
            throw new Sugar_Exception_Parse(
                $this->_tokens->getFile(),
                $this->_tokens->getLine(),
                'Internal error: operator stack is not empty after binary expression'
            );
        }

        if (count($output) != 1) {
            throw new Sugar_Exception_Parse(
                $this->_tokens->getFile(),
                $this->_tokens->getLine(),
                'Internal error: result stack does not have a single element after binary expression'
            );
        }

        // return our compiled expression
        return $output[0];
    }

    /**
     * Compiles a unary expression, or plain value, with any modifiers.
     *
     * @return Sugar_Node Expression node.
     */
    private function _compileUnaryWithModifiers()
    {
        // compile the base unary expression
        $expr = $this->_compileUnary();

        // check for a modifier chain
        if ($this->_tokens->accept('|')) {
            $pexpr = new Sugar_Node_Pipe($this->_sugar);
            $pexpr->node = $expr;
            $pexpr->modifiers = $this->_compileModifiers();
            return $pexpr;
        } else {
            return $expr;
        }
    }

    /**
     * Compiles a unary expression, or a plain value.  Not the best named
     * method.
     *
     * @return Sugar_Node Expression node.
     */
    private function _compileUnary() {
        // unary -
        if ($this->_tokens->accept('-')) {
            $expr = new Sugar_Node_Expr($this->_sugar); $expr->operator = 'negate';
            $expr->operands []= $this->_compileUnary();

        // unary !
        } elseif ($this->_tokens->accept('!')) {
            $expr = new Sugar_Node_Expr($this->_sugar);
            $expr->operator = '!';
            $expr->operands []= $this->_compileUnary();

        // no unary operator
        } else {
            $expr = $this->_compileValue();
        }

        return $expr;
    }

    /**
     * Compile a value (no operators)
     *
     * @return Sugar_Node
     */
    private function _compileValue()
    {
        // array constructor
        if ($this->_tokens->accept('[')) {
            // read in elements
            $elems = array();
            $data = true;
            $key = null;
            while (!$this->_tokens->accept(']')) {
                // read in element
                $elem = $this->_compileBinary();

                // if we have a =>, then it must be a key
                // which must be constant
                if ($this->_tokens->accept('=>')) {
                    // array keys must be constant data for now
                    if (!$elem->isLiteral()) {
                        throw new Sugar_Exception_Parse(
                            $this->_tokens->getFile(),
                            $this->_tokens->getLine(),
                            'array keys must be constants'
                        );
                    }
                    $key = $elem->value;

                    // grab actual data
                    $elem = $this->_compileBinary();

                    // put element into array
                    $elems [$key]= $elem;
                } else {
                    // add element to new array
                    $elems []= $elem;
                }

                // if not pure data, unmark data flag
                if ($data && !$elem->isLiteral()) {
                    $data = false;
                }

                // if we have a ], end
                if ($this->_tokens->accept(']')) {
                    break;
                }

                // require a comma before next item
                $this->_tokens->expect(',');
            }

            // if the data flag is true, all elements are pure data,
            // so we can push this as a value instead of an opcode
            if ($data) {
                foreach ($elems as $key=>$node) {
                    $elems [$key]= $node->value;
                }
                $expr = new Sugar_Node_Literal($this->_sugar);
                $expr->value = $elems;
            } else {
                $expr = new Sugar_Node_Array($this->_sugar);
                $expr->elements = $elems;
            }

        // sub-expression
        } elseif ($this->_tokens->accept('(')) {
            // compile sub-expression
            $expr = $this->_compileExpr();

            // ensure trailing )
            $this->_tokens->expect(')');

        // static values
        } elseif ($this->_tokens->accept(Sugar_Token::LITERAL, $data)) {
            $expr = new Sugar_Node_Literal($this->_sugar);
            $expr->value = $data;

        // vars (last item, expec it)
        } else {
            $this->_tokens->expect(Sugar_Token::VARIABLE, $name);
            $expr = new Sugar_Node_Lookup($this->_sugar);
            $expr->name = $name;
        }

        // look for referencing 'operators' for array or object accesses
        while (true) {
            // if it's an array or object . or -> op, we can also take a name
            if ($this->_tokens->accept('.') || $this->_tokens->accept('->')) {
                if ($this->_tokens->accept(Sugar_Token::IDENTIFIER, $name)) {
                    // check if this is a method call
                    if ($this->_tokens->accept('(')) {
                        // get name and parameters
                        $params = $this->_parseMethodArgs();

                        // create method call
                        $nexpr = new Sugar_Node_Call($this->_sugar);
                        $nexpr->operator = 'method';
                        $nexpr->name = $name;
                        $nexpr->file = $this->_tokens->getFile();
                        $nexpr->line = $this->_tokens->getLine();
                        $nexpr->params = $params;

                    // not a method call
                    } else {
                        // create literal node for the name
                        $lexpr = new Sugar_Node_Literal($this->_sugar);
                        $lexpr->value = $name;

                        // array lookup expression
                        $nexpr = new Sugar_Node_Expr($this->_sugar);
                        $nexpr->operator = '.';
                        $nexpr->operands []= $expr;
                        $nexpr->operands []= $lexpr;
                    }
                } else {
                    // array lookup expression
                    $nexpr = new Sugar_Node_Expr($this->_sugar);
                    $nexpr->operator = '.';
                    $nexpr->operands []= $expr;
                    $nexpr->operands []= $this->_compileUnary();
                }

                // remember expression for next loop
                $expr = $nexpr;

            // if it's an array [] operator, we need to handle the trailing ]
            } elseif ($this->_tokens->accept('[')) {
                $nexpr = new Sugar_Node_Expr($this->_sugar);
                $nexpr->operator = '.';
                $nexpr->operands []= $expr;
                $nexpr->operands []= $this->_compileExpr();
                $expr = $nexpr;

                $this->_tokens->expect(']');

            // no further array/object references
            } else {
                break;
            }
        }

        return $expr;
    }

    /**
     * Parses a modifier, not include the leading pipe.
     *
     * @return array Opcodes
     */
    private function _compileModifiers()
    {
        $modifiers = array();
        do {
            $this->_tokens->expect(Sugar_Token::IDENTIFIER, $name);

            // parse and compile modifier parameters
            $params = array();
            while ($this->_tokens->accept(':')) {
                $params []= $this->_compileUnary();
            }

            // append modifier opcodes
            $modifiers []= array('name' => $name, 'params' => $params);
        } while ($this->_tokens->accept('|'));

        return $modifiers;
    }

    /**
     * Compile an entire block, or series of statements and raw text.
     *
     * @param string $blockType Type of block being compiled (if, while,
     *                          section, etc.)
     *
     * @return array Block's bytecode.
     */
    private function _compileBlock($blockType)
    {
        $block = array();

        // build byte-code
        while (true) {
            // terminators
            if ($this->_tokens->accept(Sugar_Token::EOF)
                || $this->_tokens->acceptKeyword('else')
                || $this->_tokens->acceptKeyword('elseif')
                || $this->_tokens->acceptKeyword('end')
                || $this->_tokens->accept(Sugar_Token::END_BLOCK)
            ) {
                // return token so caller can accept/expect it
                $this->_tokens->unshift();
                break;
            }
            // literal string
            elseif ($this->_tokens->accept(Sugar_Token::DOCUMENT, $literal)) {
                $block []= array('lprint', $literal);
            }
            // if the command is empty, ignore
            elseif ($this->_tokens->accept(Sugar_Token::TERMINATOR)) {
                // do nothing
            }
            // flow control - if
            elseif ($this->_tokens->acceptKeyword('if')) {
                // get first clause expr and body
                $ops = $this->_compileExpr()->compile();
                $this->_tokens->expect(Sugar_Token::TERMINATOR);
                $body = $this->_compileBlock('if');
                $clauses = array(array($ops, $body));

                // get else/else-if clauses
                while (true) {
                    if ($this->_tokens->acceptKeyword('elseif')) {
                        // smarty-style elseif keyword
                        $ops = $this->_compileExpr()->compile();
                        $this->_tokens->expect(Sugar_Token::TERMINATOR);
                        $body = $this->_compileBlock('else-if');
                        $clauses []= array($ops, $body);
                    } elseif ($this->_tokens->acceptKeyword('else')) {
                        if ($this->_tokens->acceptKeyword('if')) {
                            // handle 'else if' construct
                            $ops = $this->_compileExpr()->compile();
                            $this->_tokens->expect(Sugar_Token::TERMINATOR);
                            $body = $this->_compileBlock('else-if');
                            $clauses []= array($ops, $body);
                        } else {
                            // plain else
                            $body = $this->_compileBlock('else');
                            $clauses []= array(false, $body);

                            // no further else/else-if blocks allowed
                            break;
                        }
                    } else {
                        break;
                    }
                }

                $this->_tokens->expectEndBlock('if');
                $this->_tokens->expect(Sugar_Token::TERMINATOR);

                // push block
                $block []= array('if', $clauses);
            }
            // while loop
            elseif ($this->_tokens->acceptKeyword('while')) {
                // get expression
                $test = $this->_compileExpr()->compile();
                $this->_tokens->expect(Sugar_Token::TERMINATOR);

                // get body
                $body = $this->_compileBlock('while');
                $this->_tokens->expectEndBlock('while');
                $this->_tokens->expect(Sugar_Token::TERMINATOR);

                // push block
                $block []= array('while', $test, $body);
            }
            // range loop
            elseif ($this->_tokens->acceptKeyword('loop')) {
                // name in lower,upper
                $this->_tokens->expect(Sugar_Token::VARIABLE, $name);
                $this->_tokens->expectKeyword('in');
                $lower = $this->_compileBinary()->compile();
                $this->_tokens->expect(',');
                $upper = $this->_compileBinary()->compile();

                // optional: ,step
                if ($this->_tokens->accept(',')) {
                    $step = $this->_compileBinary()->compile();
                } else {
                    $step = array('push', 1);
                }

                $this->_tokens->expect(Sugar_Token::TERMINATOR);

                // block
                $body = $this->_compileBlock('loop');
                $this->_tokens->expectEndBlock('loop');
                $this->_tokens->expect(Sugar_Token::TERMINATOR);

                // push block
                $block []= $lower;
                $block []= $upper;
                $block []= $step;
                $block []= array('range', $name, $body);
            }
            // loop over an array
            elseif ($this->_tokens->acceptKeyword('foreach')) {
                $key = null;
                $name = null;

                // get name
                $this->_tokens->expect(Sugar_Token::VARIABLE, $name);

                // is it a key,name pair?
                if ($this->_tokens->accept(',')) {
                    $key = $name;
                    $this->_tokens->expect(Sugar_Token::VARIABLE, $name);
                }

                // now we need the expression
                $this->_tokens->expectKeyword('in');
                $ops = $this->_compileExpr()->compile();
                $this->_tokens->expect(Sugar_Token::TERMINATOR);

                // and the block itself
                $body = $this->_compileBlock('foreach');
                $this->_tokens->expectEndBlock('foreach');
                $this->_tokens->expect(Sugar_Token::TERMINATOR);

                // store foreach block
                $block []= $ops;
                $block []= array('foreach', $key, $name, $body);
            }
            // inhibit caching
            elseif ($this->_tokens->acceptKeyword('nocache')) {
                // get block
                $body = $this->_compileBlock('nocache');
                $this->_tokens->expectEndBlock('nocache');
                $this->_tokens->expect(Sugar_Token::TERMINATOR);

                $block []= array('nocache', $body);
            }
            // if we have a var, we might have an assignment... or just an expression
            elseif ($this->_tokens->accept(Sugar_Token::VARIABLE, $name)) {
                // if it's followed by a =, it's an assignment
                if ($this->_tokens->accept('=')) {
                    $ops = $this->_compileExpr()->compile();
                    $this->_tokens->expect(Sugar_Token::TERMINATOR);

                    $block []= $ops;
                    $block []= array('assign', strtolower($name));
                }
                // otherwise, it's an expression
                else {
                    // put the variable name back, and then compile as an expression
                    $this->_tokens->pushBack();

                    // compile a print node
                    $expr = new Sugar_Node_Print($this->_sugar);
                    $expr->node = $this->_compileExpr();
                    $this->_tokens->expect(Sugar_Token::TERMINATOR);

                    // append print opcodes
                    $block []= $expr->compile();
                }
            }
            // new section?
            elseif ($this->_tokens->acceptKeyword('section')) {
                // check if insertion is requested
                $add_insert = false;
                if ($this->_tokens->accept('|')) {
                    $this->_tokens->expectKeyword('insert');
                    $add_insert = true;
                }
 
                // get section identifier
                $params = $this->_parseBuiltinFunctionArgs('section',
                        array('name'=>'string'));
                $this->_tokens->expect(Sugar_Token::TERMINATOR);
                $name = $params['name'];

                // do not allow nested sections
                if ($blockType != 'document') {
                    throw new Sugar_Exception_Parse(
                        $this->_tokens->getFile(),
                        $this->_tokens->getLine(),
                        'sections cannot be defined inside an '.$blockType.' block'
                    );
                }

                // do not allow duplicate sections
                if (isset($this->_sections[$name])) {
                    throw new Sugar_Exception_Parse(
                        $this->_tokens->getFile(),
                        $this->_tokens->getLine(),
                        'section `' . $name . '` already defined'
                    );
                }

                // parse section body
                $body = $this->_compileBlock('section');
                $this->_tokens->expectEndBlock('section');

                // store section
                $this->_sections[$name] = $body;

                // add insert instruction if requested
                if ($add_insert) {
                    $block []= array('insert', $name);
                }
            }
            // inherited layout templates
            elseif ($this->_tokens->acceptKeyword('inherit')) {
                // parse arguments; expect a single argument, 'file'
                $params = $this->_parseBuiltinFunctionArgs('inherit',
                        array('file'=>'string'));
                $this->_tokens->expect(Sugar_Token::TERMINATOR);

                // do not allow nested inherited templates
                if ($blockType != 'document') {
                    throw new Sugar_Exception_Parse(
                        $this->_tokens->getFile(),
                        $this->_tokens->getLine(),
                        'inherited template cannot be defined inside an '.$blockType.' block'
                    );
                }

                // do not more than one inherited template
                if (!empty($this->_inherit)) {
                    throw new Sugar_Exception_Parse(
                        $this->_tokens->getFile(),
                        $this->_tokens->getLine(),
                        'inherited template can only be defined once'
                    );
                }

                // store inherited template
                $this->_inherit = $params['file'];
            }
            // insert section
            elseif ($this->_tokens->acceptKeyword('insert')) {
                // parse arguments; expect a single argument, 'name'
                $params = $this->_parseBuiltinFunctionArgs('insert',
                        array('name'=>'string'));
                $this->_tokens->expect(Sugar_Token::TERMINATOR);

                // push opcode
                $block []= array('insert', $params['name']);
            }
            // we have some kind of expression to evaluate and display
            else {
                // compile a print node
                $expr = new Sugar_Node_Print($this->_sugar);
                $expr->node = $this->_compileExpr();
                $this->_tokens->expect(Sugar_Token::TERMINATOR);

                // append print opcodes
                $block []= $expr->compile();
            }
        }

        // merge the block together
        if (count($block) > 0) {
            $block = call_user_func_array('array_merge', $block);
        }

        return $block;
    }

    /**
     * Compile the given source code into bytecode.
     *
     * @param string $src  Source code to compile.
     * @param string $file Name of the file being compiled.
     *
     * @return array Bytecode.
     */
    public function compile($src, $file = '<input>')
    {
        // create tokenizer
        $this->_tokens = new Sugar_Lexer(
            $src, $file, $this->_sugar->delimStart, $this->_sugar->delimEnd
        );

        // tokenize input
        $this->_tokens->tokenize();

        // build byte-code for content section
        $bytecode = $this->_compileBlock('document');
        $this->_tokens->expect(Sugar_Token::EOF);

        // create meta-block
        $code = array(
            'type' => 'ctpl',
            'version' => Sugar::VERSION,
            'bytecode' => $bytecode,
            'inherit' => $this->_inherit,
            'sections' => $this->_sections,
        );

        // free tokenizer
        $this->_tokens = null;

        // free sections array
        $this->_sections = array();

        return $code;
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
