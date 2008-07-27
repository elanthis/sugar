<?php
function sugar_function_one($sugar, $params) {
	return 'Uno'.SugarUtil::getArg($params, 'str');
}
