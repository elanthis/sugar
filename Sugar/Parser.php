<?php
class SugarParser {
	private $tokens = null;
	private $output = array();
	private $stack = array();
	private $sugar;

	static $binops = array(
		'.' => 0,
		'*' => 1, '/' => 1, '%' => 1,
		'+' => 2, '-' => 2,
		'==' => 3, '=' => 3, '<' => 3, '>' => 3,
		'!=' => 3, '<=' => 3, '>=' => 3, 'in' => 3,
		'||' => 4, '&&' => 4,
		'(' => 10,
	);

	public function __construct (&$sugar) {
		$this->sugar =& $sugar;
	}

	private function E () {
		// expect one
		$this->P();

		// while we have a binary operator, continue chunking along
		while (($op = $this->tokens->peek()) && array_key_exists($op[0], SugarParser::$binops)) {
			$this->B();
			$this->P();
		}

		// pop remaining operators
		while ($this->stack && $this->stack[count($this->stack)-1] != '(') {
			$right = array_pop($this->output);
			$left = array_pop($this->output);
			$this->output []= array_merge($left, $right, array($this->stack[count($this->stack)-1]));
			array_pop($this->stack);
		}
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
		while ($this->stack && SugarParser::$binops[$this->stack[count($this->stack)-1]] <= SugarParser::$binops[$op]) {
			$right = array_pop($this->output);
			$left = array_pop($this->output);
			$this->output []= array_merge($left, $right, array($this->stack[count($this->stack)-1]));
			array_pop($this->stack);
		}

		// merge = to ==
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

	private function compileStmt () {
		$token = $this->tokens->peek();	

		// function call?
		if ($token[0] == 'name') {
			// remember name value
			$func = $token[1];
			$this->tokens->pop();

			// parse out parameters
			$params = array();
			while (($token = $this->tokens->peek()) && $token[0] == 'name') {
				// see if we have the equal sign
				$eq  = $this->tokens->peek(1);
				if ($eq[0] != '=')
					throw new SugarParseException($eq[2], $eq[3], 'unexpected '.SugarTokenizer::tokenName($eq).'; expected =');

				// store name, pop
				$name = $token[1];
				$this->tokens->pop(2);

				// parse the expression
				$ops = $this->compileExpr($this->tokens);

				// store parameter, set param
				$params[$name] = $ops;
			}

			return array('call', $func, $params);;

		// normal expression
		} else {
			return $this->compileExpr($this->tokens);
		}
	}

	// compile the given source code into bytecode
	public function compile ($src, $file = '<input>') {
		try {
			$this->tokens = new SugarTokenizer($src, $file);

			// build byte-code
			$blocks = array();
			$bc = array();
			while (!$this->tokens->eof()) {
				// peek at token
				$token = $this->tokens->peek();

				// eof
				if (!$token) {
					break;

				// raw string
				} elseif ($token[0] == 'literal') {
					$this->tokens->pop();

					$bc []= 'echo';
					$bc []= $token[1];
					continue;

				// if the command is empty, ignore
				} elseif ($token[0] == '%>' || $token[0] == ';') {
					// do nothing

				// print raw value
				} elseif ($token[0] == 'if') {
					$this->tokens->pop();

					$ops = $this->compileStmt($this->tokens);

					$blocks []= array('if', $bc, $ops, null, null);
					$bc = array();

				// else for if
				} elseif ($token[0] == 'else') {
					$this->tokens->pop();

					// get top block; must be an if
					$block = array_pop($blocks);
					if ($block[0] != 'if')
						throw new SugarParseException($block[2], $block[3], 'else missing if');

					// convert to an else block
					$block [0]= 'else';
					$block [3]= $bc;
					$bc = array();

					$blocks []= $block;

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
					$blocks []= array('foreach', $key, $name, $ops, $bc);
					$bc = array();

				// pop the block
				} elseif ($token[0] == 'end') {
					$this->tokens->pop();

					// get block
					$block = array_pop($blocks);

					// compile
					switch ($block[0]) {
						case 'if':
							// if block - compile
							$ops = $bc; // store block code
							$bc = array_merge($block[1], $block[2], array('if', $ops, null)); // restore old block, test
							break;
						case 'else':
							// else block - compile
							$ops = $bc; // store block code
							$bc = array_merge($block[1], $block[2], array('if', $block[3], $ops)); // restore old block, test
							break;
						case 'foreach':
							// array loop - compile
							$ops = $bc;
							$bc = array_merge($block[4], $block[3], array('foreach', $block[1], $block[2], $ops));
							break;
					}

				// print raw value
				} elseif ($token[0] == 'echo') {
					$this->tokens->pop();
					$ops = $this->compileStmt($this->tokens);

					$bc = array_merge($bc, $ops, array('print-raw'));

				// if we have a var then a =, we have an assignment
				} elseif ($token[0] == 'var' && ($t2 = $this->tokens->peek(1)) && $t2[0] == '=') {
					// remember name value
					$name = $token[1];
				
					// remove tokens, parse
					$this->tokens->pop(2);
					$ops = $this->compileStmt($this->tokens);

					$bc = array_merge($bc, $ops, array('assign', $name));

				// we have a statement
				} else {
					$ops = $this->compileStmt($this->tokens);
					$bc = array_merge($bc, $ops, array('print'));
				}

				// we should have the end token now
				$end = $this->tokens->get();
				if ($end[0] != '%>' && $end[0] != ';')
					throw new SugarParseException($end[2], $end[3], 'unexpected '.SugarTokenizer::tokenName($end).'; expected %>');
			}

		// error handler
		} catch(SugarParseException $e) {
			echo '<b>'.htmlentities($e->__toString()).'</b>';
		}

		// free tokenizer
		$this->tokens = null;

		return $bc;
	}
}
?>
