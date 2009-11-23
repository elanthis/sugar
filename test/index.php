<?php
error_reporting(E_STRICT|E_ALL);
date_default_timezone_set('UTC');

$begin = microtime(true);

$begin_load = microtime(true);
require '../Sugar.php';
$end_load = microtime(true);

// determine file to load
$file = 'index';
if (isset($_GET['t']))
	$file = $_GET['t'];

// scan available templates
$templates = array();
foreach (glob('templates/*.tpl') as $tpl) {
	if ($tpl != 'templates/layout.tpl') {
		$templates []= preg_replace(';(^templates/|[.]tpl$);', '', $tpl);
	}
}

// create parser
$begin_create = microtime(true);
$sugar = new Sugar();
$end_create = microtime(true);

// various test functions
function sugar_function_showhtml(&$sugar, $args) {
	return $args['html'];
}

function sugar_function_showtext(&$sugar, $args) {
	return $args['text'];
}

function random($sugar, $params) {
	return rand()%1000;
}

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

	function deny_acl () {
		throw new Exception("I shouldn't be run");
	}
}

// ACL test
function test_acl($smarty, $object, $method) {
	return $method != 'deny_acl';
}

// configure the sugar object
$begin_config = microtime(true);
$sugar->method_acl = 'test_acl';
$sugar->debug = isset($_GET['debug']) ? (bool)$_GET['debug'] : false;
$sugar->set('i', 10);
$sugar->set('test', 'dancing mice');
$sugar->set('html', '<b>bold</b>');
$sugar->set('list', array('one','two','three','foo'=>'bar'));
$sugar->set('obj', new Test());
$sugar->set('random', rand()%1000);
$sugar->set('newlines', "This\nhas\nnewlines!");
$sugar->set('source', $sugar->getSource($file));
$sugar->set('t', $file);
$sugar->set('templates', $templates);
$sugar->addFunction('showHtml');
$sugar->addFunction('showText');
$sugar->addFunction('random', 'random');
$sugar->addFunction('randomNoCache', 'random', false);
$sugar->addFunction('showHtmlNoEscape', 'sugar_function_showhtml', true, false);
$end_config = microtime(true);

// get some fetches
$begin_fetch = microtime(true);
$sugar->set('fetch_string', $sugar->fetchString('1+{% $i %}={% 1 + $i %}'));
$sugar->set('fetch_file', $sugar->fetch('fetch.file'));
$sugar->set('fetch_cfile', $sugar->fetchCache('fetch.file'));
$end_fetch = microtime(true);

$begin_display = microtime(true);
$sugar->displayCache('file:'.$file.'.tpl', null, null, 'file:layout.tpl');
$end_display = microtime(true);

$end = microtime(true);

$load_time = $end_load - $begin_load;
$create_time = $end_create - $begin_create;
$display_time = $end_display - $begin_display;
$config_time = $end_config - $begin_config;
$fetch_time = $end_fetch - $begin_fetch;
$total_time = $end - $begin;
$misc_time = $total_time - $load_time - $create_time - $display_time - $config_time - $fetch_time;

printf('<p style="font-size: small; color: #666; white-space: pre;">');
printf('debug:       %s<br/>', $sugar->debug?'ON (no caching)':'OFF');
printf('includes:    %0.6f seconds<br/>', $load_time);
printf('constructor: %0.6f seconds<br/>', $create_time);
printf('config:      %0.6f seconds<br/>', $config_time);
printf('fetch:       %0.6f seconds<br/>', $fetch_time);
printf('display:     %0.6f seconds<br/>', $display_time);
printf('misc.:       %0.6f seconds<br/>', $misc_time);
printf('TOTAL:       %0.6f seconds</p>', $total_time);
?>
