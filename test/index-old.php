<?php
error_reporting(E_STRICT|E_ALL);
date_default_timezone_set('UTC');

$begin = microtime(true);

$begin_include = microtime(true);
require '../Sugar.php';
$end_include = microtime(true);

// determine file to load
$file = 'index.tpl';
if (isset($_GET['t']))
	$file = $_GET['t'];

// scan available templates
$templates = array();
$dir = opendir('templates');
while ($tpl = readdir($dir)) {
	if (is_file('templates/'.$tpl) && substr($tpl, 0, 1) != '.' && $tpl != 'layout.tpl' && $tpl != 'fetch.file.tpl') {
		$templates []= $tpl;
	}
}
closedir($dir);

// create parser
$begin_create = microtime(true);
$sugar = new Sugar();
$end_create = microtime(true);

// various test functions
function sugar_function_showhtml($sugar, $args) {
	return $args['html'];
}

function sugar_function_showtext($sugar, $args) {
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

// load the template
$begin_compile = microtime(true);
$tpl = $sugar->getTemplate('file:'.$file, 'cached');
$end_compile = microtime(true);

// configure sugar and the template
$begin_config = microtime(true);
$sugar->method_acl = 'test_acl';
$sugar->debug = isset($_GET['debug']) ? (bool)$_GET['debug'] : false;
$sugar->pluginDir = dirname(__FILE__).'/plugins';
$sugar->templateDir = dirname(__FILE__).'/templates';
$sugar->cacheDir = dirname(__FILE__).'/cache';
$sugar->addFunction('showHtml');
$sugar->addFunction('showText');
$sugar->addFunction('random', 'random');
$sugar->addFunction('randomNoCache', 'random', false);
$sugar->addFunction('showHtmlNoEscape', 'sugar_function_showhtml', true, false);

$tpl->set('i', 10);
$tpl->set('test', 'dancing mice');
$tpl->set('html', '<b>bold</b>');
$tpl->set('list', array('one','two','three','foo'=>'bar'));
$tpl->set('obj', new Test());
$tpl->set('random', rand()%1000);
$tpl->set('newlines', "This\nhas\nnewlines!");

$sugar->set('source', $tpl->getSource());
$sugar->set('t', $file);
$sugar->set('templates', $templates);
$end_config = microtime(true);

// display the template
$begin_display = microtime(true);
$tpl->display();
$end_display = microtime(true);

$end = microtime(true);

$include_time = $end_include - $begin_include;
$create_time = $end_create - $begin_create;
$compile_time = $end_compile - $begin_compile;
$display_time = $end_display - $begin_display;
$config_time = $end_config - $begin_config;
$total_time = $end - $begin;
$misc_time = $total_time - $include_time - $create_time - $display_time - $config_time - $compile_time;

printf('<p style="font-size: small; color: #666; white-space: pre;">');
printf('debug:       %s<br/>', $sugar->debug?'ON (no caching)':'OFF');
printf('includes:    %0.6f seconds<br/>', $include_time);
printf('constructor: %0.6f seconds<br/>', $create_time);
printf('config:      %0.6f seconds<br/>', $config_time);
printf('compile:     %0.6f seconds<br/>', $compile_time);
printf('display:     %0.6f seconds<br/>', $display_time);
printf('misc.:       %0.6f seconds<br/>', $misc_time);
printf('TOTAL:       %0.6f seconds</p>', $total_time);
?>
