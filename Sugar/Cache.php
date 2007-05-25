<?php
interface ISugarCache {
    function stamp ($name, $id);
    function display ($name, $id);
    function store ($name, $id, $data);
    function erase ($name, $id);
    function clear ();
}

class SugarCacheHandler {
    private $sugar;
    private $output;

    public function __construct (&$sugar) {
        $this->sugar =& $sugar;
    }

    public function addOutput ($text) {
        $this->output .= $text;
    }

    public function addCall ($func, $args) {
        $this->output .= '<?php SugarRuntime::invokeNamed($sugar, "'.addslashes($func).'", unserialize("'.addslashes(serialize($args)).'")); ?>';
    }

    public function getOutput () {
        return $this->output;
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

class SugarFileCache implements ISugarCache {
    private $sugar;

    public function __construct (&$sugar) {
        $this->sugar =& $sugar;
    }

    private function cachePath ($name, $id) {
        return $this->sugar->cacheDir.'/'.md5($name.$id).'-'.str_replace('/', '-', $name).'.'.preg_replace('/[^\w]+/', '-', $id).'.cache';
    }

    public function stamp ($name, $id) {
        $path = $this->cachePath ($name, $id);
        if (file_exists($path) && is_file($path) && is_readable($path) && time()-filemtime($path)<=$this->sugar->cacheLimit)
            return filemtime($path);
        else
            return false;
    }

    public function display ($name, $id) {
        $path = $this->cachePath ($name, $id);
    
        // must exist, be readable, and not be older than $cacheLimit seconds
        if (file_exists($path) && is_file($path) && is_readable($path) && time()-filemtime($path)<=$this->sugar->cacheLimit) {
            $sugar = $this->sugar;
            require $path;
            return true;
        }

        return false;
    }

    public function store ($name, $id, $data) {
        $path = $this->cachePath ($name, $id);

        // ensure we can save the cache file
        if (!file_exists($this->sugar->cacheDir))
            throw new SugarException('cache directory does not exist: '.$this->sugar->cacheDir);
        if (!is_dir($this->sugar->cacheDir))
            throw new SugarException('cache directory is not a directory: '.$this->sugar->cacheDir);
        if (!is_writeable($this->sugar->cacheDir))
            throw new SugarException('cache directory is not writable: '.$this->sugar->cacheDir);

        // save the contents
        file_put_contents($path, $data);
        return true; 
    }

    public function erase ($name, $id) {
        $path = $this->cachePath($name, $id);
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
            if (preg_match('/^[^.].*[.]cache$/', $cache))
                unlink($this->sugar->cacheDir.'/'.$cache);

        return true;
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
