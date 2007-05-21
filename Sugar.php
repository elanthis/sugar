<?php
require_once dirname(__FILE__).'/Sugar/Exception.php';
require_once dirname(__FILE__).'/Sugar/Parser.php';
require_once dirname(__FILE__).'/Sugar/Storage.php';
require_once dirname(__FILE__).'/Sugar/Tokenizer.php';

class Sugar {
	private $vars = array();
	private $funcs = array();
	private $parser = null;

	public $storage = null;
	public $caching = true;

	public $templateDir = './templates';

	function __construct () {
		$this->storage = new SugarFileStorage($this);
		$this->parser = new SugarParser($this);
	}

	private function execute ($code) {
		$stack = array();

		try {
			for ($i = 0; $i < count($code); ++$i) {
				switch($code[$i]) {
					case 'echo':
						echo $code[++$i];
						break;
					case 'print':
						$val = array_pop($stack);
						if (is_bool($val))
							echo $val?'true':'false';
						elseif (is_array($val))
							echo htmlentities(print_r($val, true));
						else
							echo htmlentities($val);
						break;
					case 'print-raw':
						$val = array_pop($stack);
						echo $val;
						break;
					case 'push':
						$str = $code[++$i];
						$stack []= $str;
						break;
					case 'lookup':
						$var = strtolower($code[++$i]);
						$stack []= $this->vars[$var];
						break;
					case 'assign':
						$name = $code[++$i];
						$value = array_pop($stack);
						$this->set($name, $value);
						break;
					case 'negate':
						$v = array_pop($stack);
						$stack []= -$v;
						break;
					case '!':
						$v = array_pop($stack);
						$stack []= !$v;
						break;
					case '+':
						$v2 = array_pop($stack);
						$v1 = array_pop($stack);
						if (is_string($v1))
							$stack []= $v1.$v2;
						elseif (is_array($v1))
							$stack []= array_merge($v1, is_array($v2)?$v2:array($v2));
						else
							$stack []= $v1+$v2;
						break;
					case '*':
						$v2 = array_pop($stack);
						$v1 = array_pop($stack);
						$stack []= $v1 * $v2;
						break;
					case '-':
						$v2 = array_pop($stack);
						$v1 = array_pop($stack);
						$stack []= $v1 - $v2;
						break;
					case '/':
						$v2 = array_pop($stack);
						$v1 = array_pop($stack);
						$stack []= intval($v1 / $v2);
						break;
					case '%':
						$v2 = array_pop($stack);
						$v1 = array_pop($stack);
						$stack []= intval($v1 % $v2);
						break;
					case '==':
						$v2 = array_pop($stack);
						$v1 = array_pop($stack);
						$stack []= ($v1 == $v2);
					case '!=':
						$v2 = array_pop($stack);
						$v1 = array_pop($stack);
						$stack []= ($v1 != $v2);
						break;
					case '||':
						$v2 = array_pop($stack);
						$v1 = array_pop($stack);
						$stack []= ($v1 || $v2);
						break;
					case '&&':
						$v2 = array_pop($stack);
						$v1 = array_pop($stack);
						$stack []= ($v1 && $v2);
						break;
					case '<':
						$v2 = array_pop($stack);
						$v1 = array_pop($stack);
						$stack []= ($v1 < $v2);
						break;
					case '<=':
						$v2 = array_pop($stack);
						$v1 = array_pop($stack);
						$stack []= ($v1 <= $v2);
						break;
					case '>':
						$v2 = array_pop($stack);
						$v1 = array_pop($stack);
						$stack []= ($v1 >= $v2);
						break;
					case '>=':
						$v2 = array_pop($stack);
						$v1 = array_pop($stack);
						$stack []= ($v1 >= $v2);
						break;
					case 'in':
						$v2 = array_pop($stack);
						$v1 = array_pop($stack);
						$stack []= (is_array($v2) && in_array($v1, $v2));
						break;
					case 'call':
						$func = $code[++$i];
						$args = $code[++$i];
						$invoke = $this->funcs[strtolower($func)];
						if ($invoke) {
							// compile args
							$params = array();
							foreach($args as $name=>$pcode)
								$params[$name] = $this->execute($pcode);
							// call function
							$stack []= call_user_func($invoke, $params);
						} else {
							throw new SugarRuntimeException ('unknown function: '.$func);
						}
						break;
					case 'if':
						$test = array_pop($stack);
						$true = $code[++$i];
						$false = $code[++$i];
						if ($test && $true)
							$this->execute($true);
						elseif (!$test && $false)
							$this->execute($false);
						break;
					case 'foreach':
						$array = array_pop($stack);
						$key = $code[++$i];
						$name = $code[++$i];
						$block = $code[++$i];
						foreach($array as $k=>$v) {
							if ($key)
								$this->set($key, $k);
							$this->set($name, $v);
							$this->execute($block);
						}
					case '.':
						$index = array_pop($stack);
						$array = array_pop($stack);
						if (is_array($array))
							$stack []= $array[$index];
						elseif (is_object($array))
							$stack []= $array->$index;
						else
							$stack []= null;
						break;
					default:
						throw new SugarRuntimeException ('unknown opcode: '.$code[$i]);
				}
			}
		} catch (SugarRuntimeException $e) {
			echo '<b>'.htmlentities($e->__toString()).'</b>';
		}

		return $stack[0];
	}

	// set a variable
	function set ($name, $value) {
		$name = strtolower($name);
		$this->vars [$name]= $value;
	}

	// register a function; second parameter is optional real name
	function register ($name, $invoke=false) {
		if ($invoke === false)
			$invoke = $name;
		$this->funcs [strtolower($name)]= $invoke;
	}

	// compile and display given source
	function display ($file) {
		$data = $this->storage->load($file);
		if (is_string($data)) {
			$data = $this->parser->compile($data, $this->storage->getPath($file));
			if ($this->caching)
				$this->storage->store($file, $data);
		}
		$this->execute($data);
	}

	// compile and display given source
	function displayString ($source) {
		$bc = $this->parser->compile($source);
		$this->execute($bc);
	}

	// get the source code for a file
	function getSource ($file) {
		return $this->storage->getSource($file);
	}
}
?>
