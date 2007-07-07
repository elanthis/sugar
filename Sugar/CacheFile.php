<?php
class SugarCacheFile implements ISugarCache {
    private $sugar;
    private $useJson;

    public function __construct (&$sugar) {
        $this->sugar =& $sugar;
        $this->useJson = function_exists('json_encode');
    }

    private function cachePath ($name, $id, $type) {
        return $this->sugar->cacheDir.'/'.md5($name.$id).'-'.str_replace('/', '-', $name).'.'.preg_replace('/\W+/', '-', $id).'.'.preg_replace('/\W+/', '', $type);
    }

    public function stamp ($name, $id, $type) {
        $path = $this->cachePath ($name, $id, $type);
        if (file_exists($path) && is_file($path) && is_readable($path) && time()-filemtime($path)<=$this->sugar->cacheLimit)
            return filemtime($path);
        else
            return false;
    }

    public function load ($name, $id, $type) {
        $path = $this->cachePath ($name, $id, $type);
    
        // must exist, be readable, and not be older than $cacheLimit seconds
        if (file_exists($path) && is_file($path) && is_readable($path) && time()-filemtime($path)<=$this->sugar->cacheLimit) {
            // load, deserialize
            $data = file_get_contents($path);
            // decode 
            if (substr($data, 0, 1) == '[') // indicates json
                $data = json_decode($data);
            else // must be PHP serialized
                $data = unserialize($data);

            // execute
            return $data;
        } else 
            return false;

        return false;
    }

    public function store ($name, $id, $type, $data) {
        $path = $this->cachePath ($name, $id, $type);

        // ensure we can save the cache file
        if (!file_exists($this->sugar->cacheDir))
            throw new SugarException('cache directory does not exist: '.$this->sugar->cacheDir);
        if (!is_dir($this->sugar->cacheDir))
            throw new SugarException('cache directory is not a directory: '.$this->sugar->cacheDir);
        if (!is_writeable($this->sugar->cacheDir))
            throw new SugarException('cache directory is not writable: '.$this->sugar->cacheDir);

        // encode, save
        if ($this->useJson)
            $data = json_encode($data);
        else
            $data = serialize($data);
        file_put_contents($path, $data);
        return true; 
    }

    public function erase ($name, $id, $type) {
        $path = $this->cachePath($name, $id, $type);
        // if the file exists and the directory is writeable, erase it
        if (file_exists($path) && is_file($path) && is_writeable($this->sugar->cacheDir)) {
            unlink($path);
            return true;
        } else {
            return false;
        }
    }

    public function clear () {
        // direcoty rmust exist, and be both readable and writable
        if (!file_exists($this->sugar->cacheDir) || !is_dir($this->sugar->cacheDir) || !is_writable($this->sugar->cacheDir) || !is_readable($this->sugar->cacheDir))
            return false;

        $dir = opendir($this->sugar->cacheDir);
        while ($cache = readdir($dir))
            if (preg_match('/^[^.].*[.](ctpl|chtml)$/', $cache))
                unlink($this->sugar->cacheDir.'/'.$cache);

        return true;
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
