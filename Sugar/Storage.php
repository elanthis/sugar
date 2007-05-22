<?php
interface ISugarStorage {
    function load ($name);
    function store ($name, $contents);
    function getSource ($name);
}

class SugarFileStorage implements ISugarStorage {
    private $sugar;

    public $templateDir = './templates';
    public $compileDir = './templates/cache';

    public function __construct (&$sugar) {
        $this->sugar =& $sugar;
    }

    public function load ($name) {
        $path = $this->templateDir.'/'.$name.'.tpl';
        $cpath = $this->compileDir.'/'.$name.'.ctpl';

        // no such file?  end now
        if (!file_exists($path))
            return null;

        // no caching?  just return source
        if ($this->sugar->debug)
            return $this->getSource($name);

        // no cache file?  just return source
        if (!file_exists($cpath))
            return $this->getSource($name);

        // get stamps
        $sstamp = filemtime($path);
        $cstamp = filemtime($cpath);

        // return proper file
        if ($sstamp > $cstamp)
            return file_get_contents($path);
        else
            return unserialize(file_get_contents($cpath));
    }

    public function store ($name, $bc) {
        if (is_dir($this->compileDir) && is_writable($this->compileDir)) {
            file_put_contents($this->compileDir.'/'.$name.'.ctpl', serialize($bc));
            return true;
        } else {
            return false;
        }
    }

    public function getSource ($name) {
        return file_get_contents($this->templateDir.'/'.$name.'.tpl');
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
