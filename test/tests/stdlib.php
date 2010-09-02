<?php
class Sugar_Test_stdlib implements Sugar_Test {
	public function __construct()
	{
	}

	public function getExpected()
	{
		return file_get_contents(dirname(__FILE__).'/stdlib.txt');
	}
	
	public function getResult(Sugar $sugar)
	{
		$tpl = $sugar->getTemplate('stdlib.tpl');
		return $tpl->fetch();
	}
}
