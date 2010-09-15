<?php
interface Sugar_Test {
	public function getExpected();
	public function getResult(Sugar $sugar);
}

class Sugar_TestResult {
	public $test = '';
	public $status = true;
	public $exception = null;
	public $error = null;
	public $expected = '';
	public $output = '';

	public function __construct($test, $status, $error, $expected, $output) {
		$this->test = $test;
		$this->status = $status;
		$this->expected = $expected;
		$this->output = $output;

		if ($error instanceof Exception) {
			$this->error = $error->getMessage();
			$this->exception = $error;
		} else {
			$this->error = $error;
		}
	}
}

class Sugar_TestRunner {
	public function getList() {
		$tests = array();
		$dir = opendir(dirname(__FILE__).'/tests');
		while ($file = readdir($dir)) {
			if (preg_match('/^(\w+)[.]php$/', $file, $m)) {
				$tests []= $m[1];
			}
		}
		closedir($dir);
		sort($tests);
		return $tests;
	}

	public function run($test) {
		$path = dirname(__FILE__).'/tests/'.$test.'.php';
		if (!is_file($path) || !is_readable($path)) {
			return new Sugar_TestResult($test, false, "cannot open $path", '', '');
		}

		require_once $path;
		$class = "Sugar_Test_$test";
		if (!class_exists($class)) {
			return new Sugar_TestResult($test, false, "cannot find class $class", '', '');
		}
		$object = new $class;

		require_once dirname(__FILE__).'/../Sugar.php';
		$sugar = new Sugar;
		$sugar->debug = true;
		$sugar->pluginDir = dirname(__FILE__).'/plugins';
		$sugar->templateDir = array(dirname(__FILE__).'/tests', dirname(__FILE__).'/tpl');
		$sugar->cacheDir = dirname(__FILE__).'/cache';

		try {
			$expected = trim($object->getExpected());
			$result = trim($object->getResult($sugar));

			if ($expected === $result) {
				return new Sugar_TestResult($test, true, null, $expected, $result);
			} else {
				return new Sugar_TestResult($test, false, null, $expected, $result);
			}
		} catch (Exception $e) {
			return new Sugar_TestResult($test, false, $e, '', '');
		}
	}
}
