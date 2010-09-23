<?php
class Sugar_Function_Test extends Sugar_Function
{
	public $id = 0;

	public function invoke(array $params, Sugar_Context $ctx)
	{
		return ++$this->id;
	}
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
