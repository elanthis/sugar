<?php
require_once dirname(__FILE__).'/Sugar/Exception.php';
require_once dirname(__FILE__).'/Sugar/Util.php';
require_once dirname(__FILE__).'/Sugar/Ref.php';
require_once dirname(__FILE__).'/Sugar/Parser.php';
require_once dirname(__FILE__).'/Sugar/Storage.php';
require_once dirname(__FILE__).'/Sugar/Tokenizer.php';
require_once dirname(__FILE__).'/Sugar/Runtime.php';
require_once dirname(__FILE__).'/Sugar/Stdlib.php';
require_once dirname(__FILE__).'/Sugar/Cache.php';
require_once dirname(__FILE__).'/Sugar/Escaped.php';

require_once dirname(__FILE__).'/Sugar/StorageFile.php';
require_once dirname(__FILE__).'/Sugar/CacheFile.php';

// function registration flags
define('SUGAR_FUNC_NATIVE', 1);
define('SUGAR_FUNC_NO_CACHE', 2);
define('SUGAR_FUNC_SUPPRESS_RETURN', 4);

// different cache types
define('SUGAR_CACHE_TPL', 'ctpl');
define('SUGAR_CACHE_HTML', 'chtml');

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
    public $storage = array();
    public $cacheHandler = null;

    public $cache = null;
    public $debug = false;
    public $methods = false;
    public $errors = SUGAR_ERROR_PRINT;
    public $output = SUGAR_OUTPUT_HTML;
    public $defaultStorage = 'file';
    public $cacheLimit = 3600; // one hour
    public $templateDir = './templates';
    public $cacheDir = './templates/cache';

    public function __construct () {
        $this->storage ['file']= new SugarStorageFile($this);
        $this->cache = new SugarCacheFile($this);

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
        // do not escape for raw values - just return text
        if (is_a($output, 'SugarEscaped'))
            return $output->getText();

        // perform proper escaping for current mode
        switch ($this->output) {
            case SUGAR_OUTPUT_HTML:
            case SUGAR_OUTPUT_XHTML:
                return htmlentities($output);
            case SUGAR_OUTPUT_XML:
                return SugarUtil::xmlentities($output);
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

    // execute bytecode in new domain
    private function execute ($data) {
        // create new domain
        $this->vars []= array();

        try {
            $rs = SugarRuntime::execute($this, $data);

            // cleanup
            array_pop($this->vars);
            return $rs;
        } catch (Exception $e) {
            // cleanup
            array_pop($this->vars);
            throw $e;
        }
    }

    // load a template from cache or compile from source, then execute it
    private function loadExecute (SugarRef $ref) {
        // check template exists, and remember stamp
        $sstamp = $ref->storage->stamp($ref);
        if ($sstamp === false)
            throw new SugarException('template not found: '.$ref->full);

        // if compiled version does not exist or is out of date, compile it
        $cstamp = $this->cache->stamp($ref, SUGAR_CACHE_TPL);
        if ($this->debug || $sstamp >= $cstamp) {
            // compile
            $source = $ref->storage->load($ref);

            $parser = new SugarParser($this);
            $data = $parser->compile($source, $ref->storage->path($ref));
            $parser = null;

            // store
            $this->cache->store($ref, SUGAR_CACHE_TPL, $data);
        // load compiled version
        } else {
            $data = $this->cache->load($ref, SUGAR_CACHE_TPL);
        }

        // execute
        $this->execute($data);
    }

    // compile and display given source
    public function display ($file) {
        // validate name
        $ref = SugarRef::create($file, $this);
        if ($ref === false)
            throw new SugarException('illegal template name: '.$file);

        // ensure template exists
        if ($ref->storage->stamp($ref) === false)
            throw new SugarException('template not found: '.$ref->full);

        // load and run
        try {
            $this->loadExecute($ref);
            return true;
        } catch (SugarException $e) {
            $this->handleError($e);
            return false;
        }

        return true;
    }

    // check if a cache exists
    function isCached ($file, $cacheId) {
        // validate name
        $ref = SugarRef::create($file, $this, $cacheId);
        if ($ref === false)
            throw new SugarException('illegal template name: '.$file);

        return !$this->debug && $this->cache->exists($ref, SUGAR_CACHE_HTML);
    }

    // compile and display given source, with caching
    function displayCache ($file, $cacheId = null) {
        // validate name
        $ref = SugarRef::create($file, $this, $cacheId);
        if ($ref === false)
            throw new SugarException('illegal template name: '.$file);

        try {
            // get stamp, ensure template exists
            $stamp = $ref->storage->stamp($ref);
            if ($stamp === false)
                throw new SugarException('template not found: '.$ref->full);

            // get cache stamp
            $cstamp = $this->cache->stamp($ref, SUGAR_CACHE_HTML);

            // if cache exists and is up-to-date amd debug is off, run the cache
            if (!$this->debug && $cstamp > $stamp) {
                $data = $this->cache->load($ref, SUGAR_CACHE_HTML);
                $this->execute($data);
                return true;
            }

            // create cache handler if necessary
            if (!$this->cacheHandler) {
                // create cache
                $this->cacheHandler = new SugarCacheHandler($this);
                $this->loadExecute($ref);
                $cache = $this->cacheHandler->getOutput();
                $this->cacheHandler = null;

                // attempt to save cache
                $this->cache->store($ref, SUGAR_CACHE_HTML, $cache);

                // display cache
                $this->execute($cache);

            // cache handler already running - just display normally
            } else {
                $this->loadExecute($ref);
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
            $this->execute($data);
            
            return true;
        } catch (SugarException $e) {
            $this->handleError($e);
            return false;
        }
    }

    // get source code for a file
    function getSource ($file) {
        // validate name
        $ref = SugarRef::create($file, $this);
        if ($ref === false)
            throw new SugarException('illegal template name: '.$file);

        // fetch source
        return $ref->storage->load($ref);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
