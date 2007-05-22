<?php
class SugarRuntime {
	private $sugar;

	public $vars = array();
	public $funcs = array();

	public function __construct (&$sugar) {
		$this->sugar =& $sugar;
	}

	public function execute ($code) {
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
						$this->vars [$name]= $value;
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
						if (!$invoke)
							throw new SugarRuntimeException ('unknown function: '.$func);

						// compile args
						$params = array();
						foreach($args as $name=>$pcode)
							$params[$name] = $this->execute($pcode);

						// exception net
						try {
							// call function, using appropriate method
							if ($invoke[1] & SUGAR_FUNC_SIMPLE)
								$ret = call_user_func_array($invoke[0], $params);
							else
								$ret = call_user_func($invoke[0], $this, $params);

							// suppress return value if flag is set
							if ($invoke[1] & SUGAR_FUNC_SUPPRESS_RETURN)
								$ret = null;

							// store return value
							$stack []= $ret;
						} catch (Exception $e) {
							throw new SugarRuntimeException ('caught exception: '.$e->getMessage());
						}
						break;
					case 'method':
						$obj = array_pop($stack);
						$func = $code[++$i];
						$args = $code[++$i];

						if (!$this->sugar->methods)
							throw new SugarRuntimeException ('method invocation is disabled');

						if (!is_object($obj))
							throw new SugarRuntimeException ('method call on non-object');

						if (!method_exists($obj, $func))
							throw new SugarRuntimeException ('unknown method on object: '.$func);

						// compile args
						$params = array();
						foreach($args as $pcode)
							$params [] = $this->execute($pcode);

						// exception net
						try {
							$stack []= call_user_func_array(array($obj, $func), $params);
						} catch (Exception $e) {
							throw new SugarRuntimeException ('caught exception: '.$e->getMessage());
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
								$this->vars [$key]= $k;
							$this->vars [$name]= $v;
							$this->execute($block);
						}
					case '.':
						$index = array_pop($stack);
						$array = array_pop($stack);
						if (is_array($array))
							$stack []= $array[$index];
						else
							$stack []= null;
						break;
					case '->':
						$prop = array_pop($stack);
						$obj = array_pop($stack);
						if (is_object($obj))
							$stack []= $obj->$prop;
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
}
