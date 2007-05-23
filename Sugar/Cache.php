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
        $this->output .= '<?php SugarRuntime::invoke($sugar, "'.addslashes($func).'", unserialize("'.addslashes(serialize($args)).'")); ?>';
    }

    public function getOutput () {
        return $this->output;
    }
}

class SugarFileCache implements ISugarCache {
    private $sugar;

    public $cacheDir = './templates/cache';
    public $cacheLimit = 3600; // one hour

    public function __construct (&$sugar) {
        $this->sugar =& $sugar;
    }

    private function cachePath ($name, $id) {
        return $this->cacheDir.'/'.md5($name.$id).'-'.str_replace('/', '-', $name).'.'.preg_replace('/[^\w]+/', '-', $id).'.cache';
    }

    public function stamp ($name, $id) {
        $path = $this->cachePath ($name, $id);
        if (file_exists($path) && is_file($path) && is_readable($path) && time()-filemtime($path)<=$this->cacheLimit)
            return filemtime($path);
        else
            return false;
    }

    public function display ($name, $id) {
        $path = $this->cachePath ($name, $id);
    
        // must exist, be readable, and not be older than $cacheLimit seconds
        if (file_exists($path) && is_file($path) && is_readable($path) && time()-filemtime($path)<=$this->cacheLimit) {
            $sugar = $this->sugar;
            require $path;
            return true;
        }

        return false;
    }

    public function store ($name, $id, $data) {
        $path = $this->cachePath ($name, $id);

        // if the directory exists and is writable
        if (file_exists($this->cacheDir) && is_dir($this->cacheDir) && is_writeable($this->cacheDir)) {
            file_put_contents($path, $data);
            return true; 
        } else {
            return false;
        }
    }

    public function erase ($name, $id) {
        $path = $this->cachePath($name, $id);
        // if the file exists and the directory is writeable, erase it
        if (file_exists($path) && is_file($path) && is_writeable($this->cacheDir)) {
            unlink($path);
            return true;
        } else {
            return false;
        }
    }

    public function clear () {
        // direcoty rmust exist, and be both readable and writable
        if (!file_exists($this->cacheDir) || !is_dir($this->cacheDir) || !is_writable($this->cacheDir) || !is_readable($this->cacheDir))
            return false;

        $dir = opendir($this->cacheDir);
        while ($cache = readdir($dir))
            if (preg_match('/^[^.].*[.]cache$/', $cache))
                unlink($this->cacheDir.'/'.$cache);

        return true;
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
