<?php
class Sugar_Test_inherit implements Sugar_Test {
	public function __construct()
	{
	}

	public function getExpected()
	{
		return file_get_contents(dirname(__FILE__).'/inherit.txt');
	}
	
	public function getResult(Sugar $sugar)
	{
		$tpl = $sugar->getTemplate('inherit.tpl');
		return $tpl->fetch();
	}
}
