<?php
interface ISugarStorage {
    function load ($name);
    function store ($name, $contents);
    function source ($name);
}

class SugarFileStorage implements ISugarStorage {
    private $sugar;

    public $templateDir = './templates';
    public $compileDir = './templates/cache';

    public function __construct (&$sugar) {
        $this->sugar =& $sugar;
    }

    // validate a source name as being safe
    // must be only alpha-numeric and /, with no leading or trailing slash
    public static function validSourceName ($name) {
        return preg_match(';^\w+(/\w+)*$;', $name);
    }

    public function load ($name) {
        $path = $this->templateDir.'/'.$name.'.tpl';
        $cpath = $this->compileDir.'/'.$name.'.ctpl';

        // validate name
        if (!SugarFileStorage::validSourceName($name))
            throw new SugarException('invalid template name: '.$name);

        // no such file?  end now
        if (!file_exists($path))
            throw new SugarException('template not found: '.$name);

        // if caching is enabled, and the cache file exists, and its up-to-date, return the cached contents
        if (!$this->sugar->debug && file_exists($cpath) && filemtime($cpath)>=filemtime($path))
            return unserialize(file_get_contents($cpath));

        // otherwise, compile the source, cache it, and continue on
        $parser = new SugarParser($this->sugar);
        $data = $parser->compile($this->source($name));
        $parser = null;

        $this->store($name, $data);

        return $data;
    }

    public function store ($name, $bc) {
        if (is_dir($this->compileDir) && is_writable($this->compileDir)) {
            file_put_contents($this->compileDir.'/'.$name.'.ctpl', serialize($bc));
            return true;
        } else {
            return false;
        }
    }

    public function source ($name) {
        return file_get_contents($this->templateDir.'/'.$name.'.tpl');
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
