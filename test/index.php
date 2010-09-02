<?php
require_once dirname(__FILE__).'/Test.php';
require_once dirname(__FILE__).'/../Sugar.php';

// create test runner instance
$runner = new Sugar_TestRunner;

// create and configure sugar
$sugar = new Sugar;
$sugar->debug = isset($_GET['debug']) ? (bool)$_GET['debug'] : false;
$sugar->pluginDir = dirname(__FILE__).'/plugins';
$sugar->templateDir = dirname(__FILE__).'/tpl';
$sugar->cacheDir = dirname(__FILE__).'/cache';

// load index template
$tpl = $sugar->getTemplate('index.tpl');

// run tests
$tests = array();
foreach ($runner->getList() as $test) {
	$tests []= $runner->run($test);
}
$tpl->set('tests', $tests);

// display output for specific test if requests
if (isset($_GET['test'])) {
	$tpl = $sugar->getTemplate('test.tpl');
}

// render output
$tpl->display();
