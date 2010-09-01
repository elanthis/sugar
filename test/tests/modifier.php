<?php
class Sugar_Test_modifier implements Sugar_Test {
	public function __construct()
	{
	}

	public function getExpected()
	{
		return file_get_contents(dirname(__FILE__).'/../output/modifier.txt');
	}
	
	public function getResult(Sugar $sugar)
	{
		$tpl = $sugar->getTemplate('modifier.tpl');
		return $tpl->fetch();
	}
}
