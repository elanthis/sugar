<?php
class SugarException extends Exception {
    public function __construct ($msg) {
        parent::__construct('Sugar: '.$msg);
    }
}

class SugarParseException extends SugarException {
    var $file = '<input>';
    var $line = 1;
    var $msg;

    public function __construct ($file, $line, $msg) {
        parent::__construct('parse error at '.$file.','.$line.': '.$msg);
        $this->file = $file;
        $this->line = $line;
    }
}

// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
