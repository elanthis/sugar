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
$sugar->set('t', $_GET['t']);
$sugar->set('s', $_GET['s']);
$sugar->set('templates', $templates);

// various test functions
function funcFoo($args) {
	echo 'foo is here [['.$args['bar'].','.$args['baz'].','.$args['gra'].']]';
}
$sugar->register('foo', 'funcFoo');

function test($args) {
	return 'test('.$args['test'].')';
}
$sugar->register('test', 'test');

// set source variable is s is on
if ($_GET['s'])
	$sugar->set('source', '<div style="white-space: pre; border: 1px solid #000; padding: 4px; background: #eee;"><b>Source</b><br/>'.htmlentities($sugar->getSource($file)).'</div>');

// test class
class Test {
	var $one = '1-one-1';
	var $two = '2-two-2';

	function getOne () {
		return $this->one;
	}
	
	function getTwo () {
		return $this->two;
	}

	function setOne ($one) {
		$this->one = $one;
	}
}

// test variables
$sugar->set('i', 10);
$sugar->set('test', 'dancing mice');
$sugar->set('html', '<b>bold</b>');
$sugar->set('list', array('one','two','three','foo'=>'bar'));
$sugar->set('obj', new Test());

// display file
$sugar->caching = true;
$sugar->display($file);
?>
