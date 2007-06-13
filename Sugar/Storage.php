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
    private $useJson;

    public function __construct (&$sugar) {
        $this->sugar =& $sugar;
        $this->useJson = function_exists('json_encode');
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
        $cpath = $this->sugar->cacheDir.'/'.str_replace('/','.',$name).'.ctpl';

        // if caching is enabled, and the cache file exists, and its up-to-date, return the cached contents
        if (!$this->sugar->debug && is_file($cpath) && is_readable($cpath) && filemtime($cpath)>=filemtime($path)) {
            $data = file_get_contents($cpath);

            // decode 
            if (substr($data, 0, 1) == '[') // indicates json
                return json_decode($data);
            else // must be PHP serialized
                return unserialize($data);
        }

        // no cached data
        return false;
    }

    public function store ($name, $data) {
        // ensure directory is writable
        if (!is_dir($this->sugar->cacheDir))
            throw new SugarException('cache directory does not exist: '.$this->sugar->cacheDir);
        if (!is_writeable($this->sugar->cacheDir))
            throw new SugarException('cache directory is not writeable: '.$this->sugar->cacheDir);

        // encode data
        if ($this->useJson)
            $data = json_encode($data);
        else
            $data = serialize($data);

        // save
        file_put_contents($this->sugar->cacheDir.'/'.str_replace('/','.',$name).'.ctpl', $data);
        return true;
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
