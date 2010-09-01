<?php
class Sugar_Test_if implements Sugar_Test {
	public function __construct()
	{
	}

	public function getExpected()
	{
		return file_get_contents(dirname(__FILE__).'/../output/if.txt');
	}
	
	public function getResult(Sugar $sugar)
	{
		$tpl = $sugar->getTemplate('if.tpl');
		return $tpl->fetch();
	}
}
