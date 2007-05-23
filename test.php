<?php
$start = microtime(true);

require './Sugar.php';

// determine file to load
$file = 'index';
if (isset($_GET['t']))
	$file = preg_replace('/[.]tpl$/', '', $_GET['t']);

// scan available templates
$templates = preg_grep('/^[^.].*[.]tpl$/', scandir('templates'));

// create parser
$sugar = new Sugar();
$sugar->set('t', $file);
$sugar->set('s', $_GET['s']);
$sugar->set('templates', $templates);

// various test functions
function showHtml(&$sugar, $args) {
	echo $args['html'];
}
$sugar->register('showHtml');

function showText(&$sugar, $args) {
	echo $args['text'];
}
$sugar->register('showText');

function one($str='') {
	return 'Uno'.$str;
}
$sugar->register('one', 'one', SUGAR_FUNC_SIMPLE);

function random() {
	echo rand()%1000;
}
$sugar->register('random', 'random', SUGAR_FUNC_SIMPLE);
function randomNC() {
	echo rand()%1000;
}
$sugar->register('randomNC', 'randomNC', SUGAR_FUNC_SIMPLE|SUGAR_FUNC_NO_CACHE);

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
$sugar->set('source', '<div style="white-space: pre; border: 1px solid #000; padding: 4px; background: #eee;"><b>Source</b><br/>'.htmlentities($sugar->storage->source($file)).'</div>');

// test variables
$sugar->set('i', 10);
$sugar->set('test', 'dancing mice');
$sugar->set('html', '<b>bold</b>');
$sugar->set('list', array('one','two','three','foo'=>'bar'));
$sugar->set('obj', new Test());
$sugar->set('random', rand()%1000);

// display file
$sugar->debug = false;
$sugar->methods = true;
$sugar->displayCache($file, $_GET['s']);
//$sugar->display($file);

$end = microtime(true);
printf('<p>%.03f seconds</p>', $end-$start);
?>
