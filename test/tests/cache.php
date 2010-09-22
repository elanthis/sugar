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

		// load first run of each template
		$tpl = $sugar->getTemplate('cache.tpl', 'test1');
		$text1a = $tpl->fetch();

		$tpl = $sugar->getTemplate('cache.tpl', 'test2');
		$text2a = $tpl->fetch();

		// disable debug for second run
		$sugar->debug = false;

		// load second run of each template
		$tpl = $sugar->getTemplate('cache.tpl', 'test1');
		if (!$tpl->isCached()) {
			return 'isCached("cache.tpl", "test1") failed';
		}
		$text1b = $tpl->fetch();

		$tpl = $sugar->getTemplate('cache.tpl', 'test2');
		if (!$tpl->isCached()) {
			return 'isCached("cache.tpl", "test2") failed';
		}
		$text2b = $tpl->fetch();

		return $text1a.$text1b.$text2a.$text2b;
	}
}
