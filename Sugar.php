<?php
require_once dirname(__FILE__).'/Sugar/Exception.php';
require_once dirname(__FILE__).'/Sugar/Parser.php';
require_once dirname(__FILE__).'/Sugar/Storage.php';
require_once dirname(__FILE__).'/Sugar/Tokenizer.php';
require_once dirname(__FILE__).'/Sugar/Runtime.php';
require_once dirname(__FILE__).'/Sugar/Stdlib.php';
require_once dirname(__FILE__).'/Sugar/Cache.php';

// function registration flags
define('SUGAR_FUNC_NATIVE', 1);
define('SUGAR_FUNC_NO_CACHE', 2);
define('SUGAR_FUNC_SUPPRESS_RETURN', 4);

class Sugar {
    private $vars = array(array());
    private $funcs = array();
    public $cacheHandler = null;

    public $storage = null;
    public $cache = null;
    public $debug = false;
    public $methods = false;

    public function __construct () {
        $this->storage = new SugarFileStorage($this);
        $this->cache = new SugarFileCache($this);

        SugarStdlib::initialize($this);
    }

    // set a variable
    public function set ($name, $value) {
        $name = strtolower($name);
        $this->vars[count($this->vars)-1] [$name]= $value;
    }

    // register a function; second parameter is optional real name
    public function register ($name, $invoke=false, $flags=0) {
        if ($invoke === false)
            $invoke = $name;
        $this->funcs [strtolower($name)]= array($invoke, $flags);
    }

    public function getVariable ($name) {
        $name = strtolower($name);
        for ($i = count($this->vars)-1; $i >= 0; --$i)
            if (array_key_exists($name, $this->vars[$i]))
                return $this->vars[$i][$name];
        return null;
    }

    // return a function from the registered list
    public function getFunction ($name) {
        return $this->funcs[strtolower($name)];
    }

    // validate a source name as being safe
    // must be only alpha-numeric and /, with no leading or trailing slash
    public static function validTemplateName ($name) {
        return preg_match(';^\w+(?:/\w+)*$;', $name);
    }

    // execute a template, compiling if necessary
    private function execute ($file) {
        $data = $this->storage->load($file);

        // compile if necessary
        if ($data === false) {
            $parser = new SugarParser($this);
            $data = $parser->compile($this->storage->source($file), $this->storage->path($file));
            $this->storage->store($file, $data);
            $parser = null;
        }

        // execute
        $this->vars []= array();
        SugarRuntime::execute($this, $data);
        array_pop($this->vars);
    }

    // compile and display given source
    public function display ($file) {
        // validate name
        if (!Sugar::validTemplateName($file))
            throw new SugarException('illegal template name: '.$file);

        // ensure template exists
        if ($this->storage->stamp($file) === false)
            throw new SugarException('template not found: '.$file);

        // load and run
        try {
            $this->execute($file);
            return true;
        } catch (SugarException $e) {
            echo '<p><b>[['.htmlentities(get_class($e)).': '.htmlentities($e->getMessage()).']]</b></p>';
            return false;
        }

        return true;
    }

    // check if a cache exists
    function isCached ($file, $id) {
        // validate name
        if (!Sugar::validTemplateName($file))
            throw new SugarException('illegal template name: '.$file);

        return !$this->debug && $this->cache->exists($file, $id);
    }

    // compile and display given source, with caching
    function displayCache ($file, $id=true) {
        // validate name
        if (!Sugar::validTemplateName($file))
            throw new SugarException('illegal template name: '.$file);

        try {
            // get stamp, ensure template exists
            $stamp = $this->storage->stamp($file);
            if ($stamp === false)
                throw new SugarException('template not found: '.$file);

            // get cache stamp
            $cstamp = $this->cache->stamp($file, $id);

            // if cache exists and is up-to-date amd debug is off, run the cache
            if (!$this->debug && $cstamp > $stamp) {
                $this->vars []= array();
                $this->cache->display($file, $id);
                array_pop($this->vars);
                return true;
            }

            // create cache handler if necessary
            if (!$this->cacheHandler) {
                // create cache
                $this->cacheHandler = new SugarCacheHandler($this);
                $this->execute($file);
                $this->cache->store($file, $id, $this->cacheHandler->getOutput());
                $this->cacheHandler = null;

                // display cache
                $this->vars []= array();
                $this->cache->display($file, $id);
                array_pop($this->vars);

            // cache handler already running - just display normally
            } else {
                $this->execute($file);
            }

            return true;
        } catch (SugarException $e) {
            echo '<p><b>[['.htmlentities(get_class($e)).': '.htmlentities($e->getMessage()).']]</b></p>';
            return false;
        }
    }

    // compile and display given source
    function displayString ($source) {
        try {
            // compile
            $parser = new SugarParser($this);
            $data = $parser->compile($source);
            $parser = null;

            // run
            $this->vars []= array();
            SugarRuntime::execute($this, $data);
            array_pop($this->vars);
            
            return true;
        } catch (SugarException $e) {
            echo '<p><b>[['.htmlentities(get_class($e)).': '.htmlentities($e->getMessage()).']]</b></p>';
            return false;
        }
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
