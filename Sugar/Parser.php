<?php
class SugarParser {
	private $tokens = null;
	private $output = array();
	private $stack = array();
	private $sugar;

	static $binops = array(
		'.' => 0, '->' => 0,
		'*' => 1, '/' => 1, '%' => 1,
		'+' => 2, '-' => 2,
		'==' => 3, '=' => 3, '<' => 3, '>' => 3,
		'!=' => 3, '<=' => 3, '>=' => 3, 'in' => 3,
		'||' => 4, '&&' => 4,
		'(' => 11,
	);

	public function __construct (&$sugar) {
		$this->sugar =& $sugar;
	}

	private function collapseOps ($level) {
		while ($this->stack && SugarParser::$binops[$this->stack[count($this->stack)-1]] <= $level) {
			$right = array_pop($this->output);
			$left = array_pop($this->output);
			$this->output []= array_merge($left, $right, array($this->stack[count($this->stack)-1]));
			array_pop($this->stack);
		}
	}

	private function E () {
		// expect one
		$this->P();

		// while we have a binary operator, continue chunking along
		while (($op = $this->tokens->peek()) && array_key_exists($op[0], SugarParser::$binops)) {
			// method call?
			$p1 = $this->tokens->peek(1);
			$p2 = $this->tokens->peek(2);

			// method call:  expr -> name ( 
			if ($op[0] == '->' && $p1[0] == 'name' && $p2[0] == '(') {
				// store name
				$func = $p1[1];

				// read args
				$this->tokens->pop(3);
				$params = array();
				while (($token = $this->tokens->peek()) && $token[0] != ')') {
					$params []= $this->compileStmt();

					// consume trailing , if it exists
					$token = $this->tokens->peek();
					if ($token[0] == ',')
						$this->tokens->pop();
					elseif ($token[0] != ')')
						throw new SugarParseException($token[2], $token[3], 'unexpected '.SugarTokenizer::tokenName($token).'; expected ) or ,');
				}
				$this->tokens->pop();

				// form output ops
				$this->collapseOps(0);
				$obj = array_pop($this->output);
				$this->output []= array_merge($obj, array('method', $func, $params));

			// normal binary operator
			} else {
				$this->B();
				$this->P();
			}
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
		}

		// sub-expression
		elseif ($t[0] == '(') {
			// consume paren
			$this->tokens->pop();

			// push ( to mark sub-expression
			$this->stack []= '(';

			// compile sub-expression
			$this->output []= $this->compileStmt();

			// pop (
			array_pop($this->stack);

			// ensure trailing )
			$end = $this->tokens->get();
			if ($end[0] != ')')
				throw new SugarParseException($end[2], $end[3], 'unexpected '.SugarTokenizer::tokenName($end).'; expected )');
			return;
		}

		// ints
		elseif ($t[0] == 'int')
			$this->output []= array('push', intval($t[1]));
		// strings and names(treat as strings)
		elseif ($t[0] == 'string' || $t[0] == 'name')
			$this->output []= array('push', $t[1]);
		// vars
		elseif ($t[0] == 'var')
			$this->output []= array('lookup', $t[1]);

		// error
		else
			throw new SugarParseException($t[2], $t[3], 'unexpected '.SugarTokenizer::tokenName($t).'; expected value');

		// consume token
		$this->tokens->pop();
	}

	private function B () {
		$op = $this->tokens->get();
		$op = $op[0];

		// pop higher precedence operators
		$this->collapseOps(SugarParser::$binops[$op]);

		// convert = to ===
		if ($op == '=') $op = '==';

		// push op
		$this->stack []= $op;
	}

	private function U () {
		$op = $this->tokens->get();
		$op = $op[0];

		// need another P
		$this->P();
		$value = array_pop($this->output);

		// negate operator
		if ($op == '-') {
			$this->output []= array_merge($value, array('negate'));
		// not operator
		} elseif ($op == '!') {
			$this->output []= array_merge($value, array('!'));
		}
	}

	private function compileExpr () {
		$this->E();
		return array_pop($this->output);
	}

	private function isExprNext () {
		$token = $this->tokens->peek();
		return in_array($token[0], array('(', '-', '!', 'name', 'var', 'string', 'int'));
	}

	private function compileStmt () {
		$token = $this->tokens->peek();	

		// function call?
		if ($token[0] == 'name') {
			// remember name value
			$func = $token[1];
			$this->tokens->pop();

			// parse out parameters
			$params = array();
			while (($token = $this->tokens->peek()) && $token[0] == 'name' &&
					($eq = $this->tokens->peek(1)) && $eq[0] == '=') {

				// parse the expression
				$this->tokens->pop(2);
				$params[$token[1]] = $this->compileExpr($this->tokens);
			}

			// if we have no parameters, but we have an expression pending,
			// compile as a simple call
			if (empty($params)) {
				while ($this->isExprNext())
					$params []= $this->compileExpr($this->tokens);
			}

			return array('call', $func, $params);;

		// normal expression
		} else {
			return $this->compileExpr($this->tokens);
		}
	}

	// compile the given source code into bytecode
	public function compile ($src, $file = '<input>') {
		$blocks = array(
			array('main', array())
		);

		try {
			$this->tokens = new SugarTokenizer($src, $file);

			// build byte-code
			while (!$this->tokens->eof()) {
				$block =& $blocks[count($blocks)-1];

				// peek at token
				$token = $this->tokens->peek();

				// eof
				if (!$token) {
					break;

				// raw string
				} elseif ($token[0] == 'literal') {
					$this->tokens->pop();

					$block[1] []= 'echo';
					$block[1] []= $token[1];
					continue;

				// if the command is empty, ignore
				} elseif ($token[0] == '%>' || $token[0] == ';') {
					// do nothing

				// print raw value
				} elseif ($token[0] == 'if') {
					$this->tokens->pop();

					$ops = $this->compileStmt($this->tokens);

					$blocks []= array('if', array(), array(array($ops, null)));

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
						$block[2][count($block[2])-1][0] = $this->compileStmt($this->tokens);

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
					$ops = $this->compileStmt($this->tokens);

					// store foreach block
					$blocks []= array('foreach', array(), $key, $name, $ops);

				// pop the block
				} elseif ($token[0] == 'end') {
					$this->tokens->pop();

					// can't end if we're in the main block
					if ($block[0] == 'main')
						throw new SugarParseException($token[2], $token[3], 'end without an if or loop');

					// new top block
					array_pop($blocks);

					// compile
					switch ($block[0]) {
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
					$block =& $blocks[count($blocks)-1];
					$block[1] = array_merge($block[1], $bc);

				// print raw value
				} elseif ($token[0] == 'echo') {
					$this->tokens->pop();
					$ops = $this->compileStmt($this->tokens);

					$block[1] = array_merge($block[1], $ops, array('print-raw'));

				// if we have a var then a =, we have an assignment
				} elseif ($token[0] == 'var' && ($t2 = $this->tokens->peek(1)) && $t2[0] == '=') {
					// remember name value
					$name = $token[1];
				
					// remove tokens, parse
					$this->tokens->pop(2);
					$ops = $this->compileStmt($this->tokens);

					$block[1] = array_merge($block[1], $ops, array('assign', strtolower($name)));

				// we have a statement
				} else {
					$ops = $this->compileStmt($this->tokens);
					$block[1] = array_merge($block[1], $ops, array('print'));
				}

				// we should have the end token now
				$end = $this->tokens->get();
				if ($end[0] != '%>' && $end[0] != ';')
					throw new SugarParseException($end[2], $end[3], 'unexpected '.SugarTokenizer::tokenName($end).'; expected %>');
			}

			// still in a block?
			if (count($blocks) != 1)
				throw new SugarParseException($end[2], $end[3], 'unxpected end of file; expected end');

		// error handler
		} catch(SugarParseException $e) {
			echo '<b>'.htmlentities($e->__toString()).'</b>';
		}

		// free tokenizer
		$this->tokens = null;

		return $blocks[0][1];
	}
}
?>
