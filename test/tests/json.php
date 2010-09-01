<?php
class Sugar_Test_json implements Sugar_Test {
	public function __construct()
	{
	}

	public function getExpected()
	{
		return file_get_contents(dirname(__FILE__).'/../output/json.txt');
	}
	
	public function getResult(Sugar $sugar)
	{
		$tpl = $sugar->getTemplate('json.tpl');
		return $tpl->fetch();
	}
}
