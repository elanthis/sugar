<?php
class MyObject {
	public $foo = 'foo';
	public $bar = array(1, 2, 3);

	public function method($p1, $p2, $p3)
	{
		return "p1=$p1 p2=$p2 p3=$p3";
	}

	public function unsafe()
	{
		return 'fail';
	}
}

function MyMethodAcl($sugar, $object, $name, $params)
{
	return $name != 'unsafe';
}

class Sugar_Test_object implements Sugar_Test {
	public function __construct()
	{
	}

	public function getExpected()
	{
		return file_get_contents(dirname(__FILE__).'/object.txt');
	}
	
	public function getResult(Sugar $sugar)
	{
		$sugar->methodAcl = 'MyMethodAcl';
		$sugar->set('object', new MyObject);

		$tpl = $sugar->getTemplate('object.tpl');
		return $tpl->fetch();
	}
}
