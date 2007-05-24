<?php
interface ISugarStorage {
    function stamp ($name);
    function load ($name);
    function store ($name, $data);
    function source ($name);
    function path ($name);
}

class SugarFileStorage implements ISugarStorage {
    private $sugar;

    public function __construct (&$sugar) {
        $this->sugar =& $sugar;
    }

    public function stamp ($name) {
        $path = $this->sugar->templateDir.'/'.$name.'.tpl';
        if (is_file($path) && is_readable($path))
            return filemtime($path);
        else
            return false;
    }

    public function load ($name) {
        $path = $this->sugar->templateDir.'/'.$name.'.tpl';
        $cpath = $this->sugar->compileDir.'/'.$name.'.ctpl';

        // if caching is enabled, and the cache file exists, and its up-to-date, return the cached contents
        if (!$this->sugar->debug && is_file($cpath) && is_readable($cpath) && filemtime($cpath)>=filemtime($path))
            return unserialize(file_get_contents($cpath));

        // no cached data
        return false;
    }

    public function store ($name, $data) {
        if (is_dir($this->sugar->compileDir) && is_writable($this->sugar->compileDir)) {
            file_put_contents($this->sugar->compileDir.'/'.$name.'.ctpl', serialize($data));
            return true;
        } else {
            return false;
        }
    }

    public function source ($name) {
        $path = $this->sugar->templateDir.'/'.$name.'.tpl';
        if (is_file($path) && is_readable($path))
            return file_get_contents($path);
        else
            return false;
    }

    public function path ($name) {
        return $this->sugar->templateDir.'/'.$name.'.tpl';
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
