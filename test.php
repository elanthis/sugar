<?php
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

// test class
class Test {
	var $bar = 'BAR';

	function foo () {
		return 3;
	}

	function doit ($one, $two, $three) {
		return '[['.$one.','.$two.','.$three.']]';
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

// display file
$sugar->caching = false;
$sugar->methods = true;
$sugar->display($file);
?>
