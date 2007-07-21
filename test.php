<?php
$start = microtime(true);

$begin_load = microtime(true);
require './Sugar.php';
$end_load = microtime(true);

// determine file to load
$file = 'index';
if (isset($_GET['t']))
	$file = $_GET['t'];

// scan available templates
$templates = preg_replace('/[.]tpl$/', '', preg_grep('/^[^.].*[.]tpl$/', scandir('templates')));

// create parser
$sugar = new Sugar();
$sugar->set('t', $file);
$sugar->set('templates', $templates);

// various test functions
function showHtml(&$sugar, $args) {
	return new SugarEscaped($args['html']);
}
$sugar->register('showHtml');

function showText(&$sugar, $args) {
	return $args['text'];
}
$sugar->register('showText');

function one($str='') {
	return 'Uno'.$str;
}
$sugar->register('one', 'one', SUGAR_FUNC_NATIVE);

function random() {
	return rand()%1000;
}
$sugar->register('random', 'random', SUGAR_FUNC_NATIVE);

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

// display file
$sugar->debug = false;
$sugar->methods = true;

$begin_display = microtime(true);
$sugar->displayCache('file:'.$file.'.tpl');
//$sugar->display($file);
$end_display = microtime(true);

$end = microtime(true);
printf('<p>total: %0.6f seconds</p>', $end-$start);
printf('<p>include: %0.6f seconds</p>', $end_load-$begin_load);
printf('<p>display: %0.6f seconds</p>', $end_display-$begin_display);
?>
