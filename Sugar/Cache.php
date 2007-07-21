<?php
interface ISugarCache {
    function stamp (SugarRef $ref, $type);
    function load (SugarRef $ref, $type);
    function store (SugarRef $ref, $type, $data);
    function erase (SugarRef $ref, $type);
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

    public function addBlock ($block) {
        $this->compact();
        array_push($this->bc, 'nocache', $block);
    }

    public function getOutput () {
        $this->compact();
        return $this->bc;
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
