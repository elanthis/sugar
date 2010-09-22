<?php
function sugar_function_test($sugar, $params)
{
	static $id = 0;
	return ++$id;
}

class Sugar_Test_cache implements Sugar_Test {
	public function __construct()
	{
	}

	public function getExpected()
	{
		return file_get_contents(dirname(__FILE__).'/cache.txt');
	}
	
	public function getResult(Sugar $sugar)
	{
		$sugar->addFunction('test', 'sugar_function_test', false);

		$tpl = $sugar->getTemplate('cache.tpl', 'test1');
		$text1 = $tpl->fetch();

		$tpl = $sugar->getTemplate('cache.tpl', 'test2');
		$text2 = $tpl->fetch();

		return $text1.$text1.$text2.$text2;
	}
}
