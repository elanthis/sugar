<?php
interface ISugarCache {
    function stamp ($name, $id, $type);
    function load ($name, $id, $type);
    function store ($name, $id, $type, $data);
    function erase ($name, $id, $type);
    function clear ();
}

class SugarCacheHandler {
    private $sugar;
    private $output;
    private $bc;

    private function compact () {
        if ($this->output) {
            $this->bc []= 'echo';
            $this->bc []= $this->output;
            $this->output = '';
        }
    }

    public function __construct (&$sugar) {
        $this->sugar =& $sugar;
    }

    public function addOutput ($text) {
        $this->output .= $text;
    }

    public function addCall ($func, $args) {
        $this->compact();
        array_push($this->bc, 'cinvoke', $func, $args);
    }

    public function getOutput () {
        $this->compact();
        return $this->bc;
    }

    public function beginCache () {
        ob_start();
    }

    public function endCache ($ignore = false) {
        if (!$ignore)
            $this->output .= ob_get_contents();
        ob_end_clean();
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
