<?php
require_once dirname(__FILE__).'/Sugar/Exception.php';
require_once dirname(__FILE__).'/Sugar/Parser.php';
require_once dirname(__FILE__).'/Sugar/Storage.php';
require_once dirname(__FILE__).'/Sugar/Tokenizer.php';
require_once dirname(__FILE__).'/Sugar/Runtime.php';
require_once dirname(__FILE__).'/Sugar/Stdlib.php';
require_once dirname(__FILE__).'/Sugar/Cache.php';

// function registration flags
define('SUGAR_FUNC_SIMPLE', 1);
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

    function __construct () {
        $this->storage = new SugarFileStorage($this);
        $this->cache = new SugarFileCache($this);

        SugarStdlib::initialize($this);
    }

    // set a variable
    function set ($name, $value) {
        $name = strtolower($name);
        $this->vars[count($this->vars)-1] [$name]= $value;
    }

    function get ($name) {
        $name = strtolower($name);
        for ($i = count($this->vars)-1; $i >= 0; --$i)
            if (array_key_exists($name, $this->vars[$i]))
                return $this->vars[$i][$name];
        return null;
    }

    // register a function; second parameter is optional real name
    function register ($name, $invoke=false, $flags=0) {
        if ($invoke === false)
            $invoke = $name;
        $this->funcs [strtolower($name)]= array($invoke, $flags);
    }

    // return a function from the registered list
    function getFunction ($name) {
        return $this->funcs[strtolower($name)];
    }

    // compile and display given source
    function display ($file) {
        // ensure template exists
        if (!$this->storage->exists($file)) {
            echo '<p><b>Sugar Error: template not found: '.htmlentities($file).'</b></p>';
            return false;
        }

        // load and run
        try {
            $data = $this->storage->load($file);
            $this->vars []= array();
            SugarRuntime::run($this, $data);
            array_pop($this->vars);

            return true;
        } catch (SugarException $e) {
            echo '<p><b>'.htmlentities($e->__toString()).'</b></p>';
            return false;
        }

        return true;
    }

    // check if a cache exists
    function isCached ($file, $id) {
        return $this->cache->exists($file, $id);
    }

    // compile and display given source, with caching
    function displayCache ($file, $id) {
        try {
            // if the cache exists, just run that
            if ($this->cache->exists($file, $id)) {
                $this->vars []= array();
                $this->cache->load($file, $id);
                array_pop($this->vars);
                return true;
            }

            // ensure template exists
            if (!$this->storage->exists($file)) {
                echo '<p><b>Sugar Error: template not found: '.htmlentities($file).'</b></p>';
                return false;
            }

            // create cache handler if necessary
            if (!$this->cacheHandler) {
                $this->cacheHandler = new SugarCacheHandler($this);

                // cache
                $data = $this->storage->load($file);
                $this->vars []= array();
                SugarRuntime::makeCache($this, $data);
                array_pop($this->vars);
                $this->cache->store($file, $id, $this->cacheHandler->getOutput());
                $this->cacheHandler = null;

                // run cache
                $this->vars []= array();
                $this->cache->load($file, $id);
                array_pop($this->vars);

            // cache handler already running - just display normally
            } else {
                $data = $this->storage->load($file);
                $this->vars []= array();
                SugarRuntime::run($this, $data);
                array_pop($this->vars);

                return true;
            }

            return true;
        } catch (SugarException $e) {
            echo '<p><b>'.htmlentities($e->__toString()).'</b></p>';
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
            SugarRuntime::run($this, $data);
            array_pop($this->vars);
            
            return true;
        } catch (SugarException $e) {
            echo '<p><b>'.htmlentities($e->__toString()).'</b></p>';
            return false;
        }
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
