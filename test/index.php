<?php
error_reporting(E_STRICT|E_ALL);
date_default_timezone_set('UTC');

$start = microtime(true);

$begin_load = microtime(true);
require '../Sugar.php';
$end_load = microtime(true);

// determine file to load
$file = 'index';
if (isset($_GET['t']))
	$file = $_GET['t'];

// scan available templates
$templates = preg_replace('/[.]tpl$/', '', preg_grep('/^[^.]+[.]tpl$/', scandir('templates')));

// create parser
$begin_create = microtime(true);
$sugar = new Sugar();
$end_create = microtime(true);

// setup
$sugar->set('t', $file);
$sugar->set('templates', $templates);

// various test functions
function sugar_function_showhtml(&$sugar, $args) {
	return new SugarEscaped($args['html']);
}
$sugar->addFunction('showHtml');

function sugar_function_showtext(&$sugar, $args) {
	return $args['text'];
}
$sugar->addFunction('showText');

function random($sugar, $params) {
	return rand()%1000;
}
$sugar->addFunction('random', 'random');

// test class
class Test {
	var $bar = 'BAR';

	function foo () {
		return 3;
	}

	function doit ($one, $two, $three) {
		return '[['.$one.','.$two.','.$three.']]';
	}

	function fail () {
		throw new Exception("fail() called");
	}
}

// set source variable is s is on
$sugar->set('source', '<div style="white-space: pre; border: 1px solid #000; padding: 4px; background: #eee;"><b>Source</b><br/>'.htmlentities($sugar->getSource($file)).'</div>');

// test variables
$sugar->set('i', 10);
$sugar->set('test', 'dancing mice');
$sugar->set('html', '<b>bold</b>');
$sugar->set('list', array('one','two','three','foo'=>'bar'));
$sugar->set('obj', new Test());
$sugar->set('random', rand()%1000);
$sugar->set('newlines', "This\nhas\nnewlines!");

// fetch testing
$sugar->set('fetch_string', $sugar->fetchString('1+{% $i %}={% 1 + $i %}'));
$sugar->set('fetch_file', $sugar->fetch('fetch.file'));
$sugar->set('fetch_cfile', $sugar->fetchCache('fetch.file'));

// display file
$sugar->debug = isset($_GET['debug']) ? (bool)$_GET['debug'] : false;

$begin_display = microtime(true);
$sugar->displayCache('file:'.$file.'.tpl');
//$sugar->display($file);
$end_display = microtime(true);

$end = microtime(true);
printf('<p style="font-size: small; color: #666; white-space: pre;">');
printf('debug:       %s<br/>', $sugar->debug?'ON (no caching)':'OFF');
printf('includes:    %0.6f seconds<br/>', $end_load-$begin_load);
printf('constructor: %0.6f seconds<br/>', $end_create-$begin_create);
printf('display():   %0.6f seconds<br/>', $end_display-$begin_display);
printf('misc.:       %0.6f seconds<br/>', ($end-$start)-($end_load-$begin_load)-($end_create-$begin_create)-($end_display-$begin_display));
printf('TOTAL:       %0.6f seconds</p>', $end-$start);
?>
