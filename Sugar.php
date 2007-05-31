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

// error handling method
define('SUGAR_ERROR_PRINT', 1);
define('SUGAR_ERROR_THROW', 2);
define('SUGAR_ERROR_DIE', 3);
define('SUGAR_ERROR_IGNORE', 4);

// output mode
define('SUGAR_OUTPUT_HTML', 1);
define('SUGAR_OUTPUT_XHTML', 2);
define('SUGAR_OUTPUT_XML', 3);
define('SUGAR_OUTPUT_TEXT', 4);

class Sugar {
    private $vars = array(array());
    private $funcs = array();
    private $storage = array();
    public $cacheHandler = null;

    public $cache = null;
    public $debug = false;
    public $methods = false;
    public $errors = SUGAR_ERROR_PRINT;
    public $output = SUGAR_OUTPUT_HTML;
    public $defaultStorage = 'file';
    public $cacheLimit = 3600; // one hour
    public $templateDir = './templates';
    public $compileDir = './templates/compiled';
    public $cacheDir = './templates/cache';

    public function __construct () {
        $this->storage ['file']= new SugarFileStorage($this);
        $this->cache = new SugarFileCache($this);

        SugarStdlib::initialize($this);
    }

    // set a variable
    public function set ($name, $value) {
        $name = strtolower($name);
        $this->vars[count($this->vars)-1] [$name]= $value;
        return true;
    }

    // register a function; second parameter is optional real name
    public function register ($name, $invoke=false, $flags=0) {
        if ($invoke === false)
            $invoke = $name;
        $this->funcs [strtolower($name)]= array($invoke, $flags);
        return true;
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

    // add a new storage type
    public function addStorage ($name, &$driver) {
        $this->storage [$name]= &$driver;
        return true;
    }

    // escape output based on current mode
    public function escape ($output) {
        switch ($this->output) {
            case SUGAR_OUTPUT_HTML:
            case SUGAR_OUTPUT_XHTML:
                return htmlentities($output);
            case SUGAR_OUTPUT_XML:
                return SugarRuntime::xmlentities($output);
            case SUGAR_OUTPUT_TEXT:
            default:
                return $output;
        }
    }

    // handle errors
    public function handleError ($e) {
        // if in throw mode, re-throw the exception
        if ($this->errors == SUGAR_ERROR_THROW)
            throw $e;

        // if in ignore mode, just return
        if ($this->errors == SUGAR_ERROR_IGNORE)
            return;

        // print the error
        echo "\n[[ ".$this->escape(get_class($e)).': '.$this->escape($e->getMessage())." ]]\n";

        // die if in die mode
        if ($this->errors == SUGAR_ERROR_DIE)
            die();
    }

    // validate a source name as being safe
    // must be only alpha-numeric and /, with no leading or trailing slash
    private function parseName ($name) {
        // parse out parts
        if (!preg_match(';^(?:(\w+):)?(/?\w+(?:/\w+)*)(?:[.]tpl)?$;', $name, $ar))
            return false;

        // determine scheme; set to default is necessary
        $scheme = $ar[1];
        if (!$scheme)
            $scheme = $this->defaultStorage;

        // validate scheme exists
        if (!isset($this->storage[$scheme]))
            return false;

        return array($this->storage[$scheme], $ar[2]);
    }

    // 

    // execute a template, compiling if necessary
    private function execute ($file) {
        $ref =& $this->parseName($file);
        if ($ref === false)
            throw new SugarException('illegal template name: '.$file);

        $data = $ref[0]->load($ref[1]);

        // compile if necessary
        if ($data === false) {
            $parser = new SugarParser($this);
            $data = $parser->compile($ref[0]->source($ref[1]), $ref[0]->path($ref[1]));
            $ref[0]->store($ref[1], $data);
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
        $ref =& $this->parseName($file);
        if ($ref === false)
            throw new SugarException('illegal template name: '.$file);

        // ensure template exists
        if ($ref[0]->stamp($ref[1]) === false)
            throw new SugarException('template not found: '.$file);

        // load and run
        try {
            $this->execute($file);
            return true;
        } catch (SugarException $e) {
            $this->handleError($e);
            return false;
        }

        return true;
    }

    // check if a cache exists
    function isCached ($file, $id) {
        // validate name
        if (!$this->parseName($file))
            throw new SugarException('illegal template name: '.$file);

        return !$this->debug && $this->cache->exists($file, $id);
    }

    // compile and display given source, with caching
    function displayCache ($file, $id=true) {
        // validate name
        $ref =& $this->parseName($file);
        if ($ref === false)
            throw new SugarException('illegal template name: '.$file);

        try {
            // get stamp, ensure template exists
            $stamp = $ref[0]->stamp($ref[1]);
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
                $cache = $this->cacheHandler->getOutput();
                $this->cacheHandler = null;

                // attempt to save cache
                $this->cache->store($file, $id, $cache);

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
            $this->handleError($e);
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
            $this->handleError($e);
            return false;
        }
    }

    // get source code for a file
    function getSource ($file) {
        // validate name
        $ref =& $this->parseName($file);
        if ($ref === false)
            throw new SugarException('illegal template name: '.$file);

        // fetch source
        return $ref[0]->source($ref[1]);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
