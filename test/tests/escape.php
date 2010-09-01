<?php
class Sugar_Test_escape implements Sugar_Test {
	public function __construct()
	{
	}

	public function getExpected()
	{
		return file_get_contents(dirname(__FILE__).'/../output/escape.txt');
	}
	
	public function getResult(Sugar $sugar)
	{
		$tpl = $sugar->getTemplate('escape.tpl');
		return $tpl->fetch();
	}
}
