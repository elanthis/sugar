<?php
class Sugar_Test_array implements Sugar_Test {
	public function __construct()
	{
	}

	public function getExpected()
	{
		return file_get_contents(dirname(__FILE__).'/../output/array.txt');
	}
	
	public function getResult(Sugar $sugar)
	{
		$tpl = $sugar->getTemplate('array.tpl');
		return $tpl->fetch();
	}
}
