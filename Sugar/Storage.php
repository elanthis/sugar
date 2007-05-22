<?php
interface ISugarStorage {
    function load ($name);
    function store ($name, $contents);
    function getSource ($name);
}

class SugarFileStorage implements ISugarStorage {
    private $sugar;

    public function __construct (&$sugar) {
        $this->sugar =& $sugar;
    }

    public function getPath ($name) {
        return $this->sugar->templateDir . '/' . $name . '.tpl';
    }

    public function getCache ($name) {
        return $this->sugar->templateDir . '/cache/' . $name . '.ctpl';
    }

    public function load ($name) {
        // no such file?  end now
        if (!file_exists($this->getPath($name)))
            return null;

        // no caching?  just return source
        if (!$this->sugar->caching)
            return $this->getSource($name);

        // no cache file?  just return source
        if (!file_exists($this->getCache($name)))
            return $this->getSource($name);

        // get stamps
        $sstamp = filemtime($this->getPath($name));
        $cstamp = filemtime($this->getCache($name));

        // return proper file
        if ($sstamp > $cstamp)
            return $this->getSource($name);
        else
            return unserialize(file_get_contents($this->getCache($name)));
    }

    public function store ($name, $bc) {
        $dir = $this->sugar->templateDir.'/cache/';
        if (is_dir($dir) && is_writable($dir)) {
            file_put_contents($this->getCache($name), serialize($bc));
            return true;
        } else {
            return false;
        }
    }

    public function getSource ($name) {
        return file_get_contents($this->getPath($name));
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
