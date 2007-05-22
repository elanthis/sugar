<?php
class SugarStdlib {
    public static function _include (&$sugar, $params) {
        $sugar->display($params[0]);
    }

    public static function _eval (&$sugar, $params) {
        $sugar->displayString($params[0]);
    }

    public static function _echo (&$sugar, $params) {
        echo SugarRuntime::showValue($params[0]);
    }

    public static function initialize (&$sugar) {
        $sugar->register('include', array('SugarStdlib', '_include'));
        $sugar->register('eval', array('SugarStdlib', '_eval'));
        $sugar->register('echo', array('SugarStdlib', '_echo'));
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
