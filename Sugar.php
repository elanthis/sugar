<?php
/**
 * PHP-Sugar Template Engine
 * Copyright (c) 2007  AwesomePlay Productions, Inc. and
 * contributors.  All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @package Sugar
 * @author Sean Middleditch
 */

/**
 * Directory in which PHP-Sugar is installed.  Used for including
 * additional PHP-Sugar source files.
 */
define('SUGAR_ROOTDIR', dirname(__FILE__));

/**#@+
 * Core includes.
 */
require_once SUGAR_ROOTDIR.'/Sugar/Exception.php';
require_once SUGAR_ROOTDIR.'/Sugar/Ref.php';
require_once SUGAR_ROOTDIR.'/Sugar/Storage.php';
require_once SUGAR_ROOTDIR.'/Sugar/Cache.php';
/**#@-*/

/**#@+
 * Drivers.
 */
require_once SUGAR_ROOTDIR.'/Sugar/StorageFile.php';
require_once SUGAR_ROOTDIR.'/Sugar/CacheFile.php';
/**#@-*/

/**#@+
 * Utility includes.
 */
require_once SUGAR_ROOTDIR.'/Sugar/Escaped.php';
require_once SUGAR_ROOTDIR.'/Sugar/Util.php';
/**#@-*/

/**
 * PHP-Sugar Standard Library.
 */
require_once SUGAR_ROOTDIR.'/Sugar/Stdlib.php';

/**
 * Version of PHP-Sugar.
 */
define('SUGAR_VERSION', '0.71');

/**
 * Pass this flag to {@link Sugar::register} to indicate that the function
 * uses the native PHP function call syntax, instead of the PHP-Sugar
 * syntax.
 */
define('SUGAR_FUNC_NATIVE', 1);

/**
 * Pass this flag to {@link Sugar::register} to indicate that the return
 * value of the function should not be printed by default when called as a
 * top-level function.  This flag has no effect when the function is called
 * as part of an expression.
 */
define('SUGAR_FUNC_SUPPRESS_RETURN', 2);

/**#@+
 * Cache classifiers.
 *
 * @internal
 */
define('SUGAR_CACHE_TPL', 'ctpl');
define('SUGAR_CACHE_HTML', 'chtml');
/**#@-*/

/**#@+
 * Error handling method to be used by Sugar.
 * - SUGAR_ERROR_PRINT: print errors, do not throw exceptions
 * - SUGAR_ERROR_THROW: throw exceptions, do not print anything
 * - SUGAR_ERROR_DIE: print errors, then call die()
 * - SUGAR_ERROR_IGNORE: do not print errors, do not throw exceptions
 */
define('SUGAR_ERROR_PRINT', 1);
define('SUGAR_ERROR_THROW', 2);
define('SUGAR_ERROR_DIE', 3);
define('SUGAR_ERROR_IGNORE', 4);
/**#@-*/

/**#@+
 * Specifies the output escaping method which should be used.
 */
define('SUGAR_OUTPUT_HTML', 1);
define('SUGAR_OUTPUT_XHTML', 2);
define('SUGAR_OUTPUT_XML', 3);
define('SUGAR_OUTPUT_TEXT', 4);
/**#@-*/

/**
 * PHP-Sugar core class.
 *
 * Instantiate this class to use PHP-Sugar.
 * @package Sugar
 * @author Sean Middleditch
 */
class Sugar {
    /**#@+
     * Internal book-keeping.
     *
     * @var array
     */
    private $vars = array(array());
    private $funcs = array();
    public $storage = array();
    /**#@-*/

    /**
     * Cache management.  Used internally.
     *
     * @var SugarCacheHandler
     */
    public $cacheHandler = null;

    /**
     * This is the cache driver to use for storing bytecode and HTML caches.
     * This is initialized to the {@link SugarCacheFile} driver by default.
     *
     * @var ISugarCache
     */
    public $cache = null;

    /**
     * Setting this to true will disable all caching, forcing every template
     * to be recompiled and executed on every load.
     *
     * @var bool
     */
    public $debug = false;

    /**
     * Setting this to true will allow for methods to be called on object
     * variables within templates.  This is disabled by default for security
     * reasons.
     *
     * @var bool
     */
    public $methods = false;

    /**
     * This is the error handling method Sugar should use.  By default,
     * errors are echoed to the screen and no exceptions are thrown.  Set
     * this to one of the following:
     * - {@link SUGAR_ERROR_THROW}
     * - {@link SUGAR_ERROR_PRINT}
     * - {@link SUGAR_ERROR_DIE}
     * - {@link SUGAR_ERROR_IGNORE}
     *
     * @var int
     */
    public $errors = SUGAR_ERROR_PRINT;

    /**
     * This is the output escaping method to be used.  This is necessary
     * for many formats, such as XML and HTML, to ensure that special
     * are escaped properly.
     * - {@link SUGAR_OUTPUT_HTML}
     * - {@link SUGAR_OUTPUT_XHTML}
     * - {@link SUGAR_OUTPUT_XML}
     * - {@link SUGAR_OUTPUT_TEXT}
     *
     * @var int
     */
    public $output = SUGAR_OUTPUT_HTML;

    /**
     * This is the default storage driver to use when no storage driver
     * is specified as part of a template name.
     *
     * @var string
     */
    public $defaultStorage = 'file';

    /**
     * Maximum age of HTML caches in seconds.
     *
     * @var int
     */
    public $cacheLimit = 3600; // one hour

    /**
     * Directory in which templates can be found when using the file storage
     * driver.
     *
     * @var string
     */
    public $templateDir = './templates';

    /**
     * Directory in which bytecode and HTML caches can be stored when using
     * the file cache driver.
     *
     * @var string
     */
    public $cacheDir = './templates/cache';

    /**
     * Character set that output should be in.
     *
     * @var string
     */
    public $charset = 'ISO-8859-1';

    /**
     * Constructor
     */
    public function __construct () {
        $this->storage ['file']= new SugarStorageFile($this);
        $this->cache = new SugarCacheFile($this);

        SugarStdlib::initialize($this);
    }

    /**
     * Set a new variable to be available within templates.
     *
     * @param string $name The variable's name.
     * @param mixed $value The variable's value.
     * @return bool true on success
     */
    public function set ($name, $value) {
        $name = strtolower($name);
        $this->vars[count($this->vars)-1] [$name]= $value;
        return true;
    }

    /**
     * Registers a new function to be available within templates.
     *
     * @param string $name The name to register the function under.
     * @param callback $invoke Optional PHP callback; if null, the $name parameter is used as the callback.
     * @param int $flags Bitset including {@link SUGAR_FUNC_SUPPRESS_RETURN} or {@link SUGAR_FUNC_NATIVE}.
     * @return bool true on success
     */
    public function register ($name, $invoke=null, $flags=0) {
        if (!$invoke)
            $invoke = $name;
        $this->funcs [strtolower($name)]= array($invoke, $flags);
        return true;
    }

    /**
     * Looks up the current value of a variable.
     *
     * @param string $name Name of the variable to lookup.
     * @return mixed
     */
    public function getVariable ($name) {
        $name = strtolower($name);
        for ($i = count($this->vars)-1; $i >= 0; --$i)
            if (array_key_exists($name, $this->vars[$i]))
                return $this->vars[$i][$name];
        return null;
    }

    /**
     * Returns an array containing the data for a registered function.  The
     * first field of the array is the callback, and the second field are
     * the function flags.
     *
     * @param string $name Function name to lookup.
     * @return array
     */
    public function getFunction ($name) {
        return $this->funcs[strtolower($name)];
    }

    /**
     * Register a new storage driver.
     *
     * @param string $name Name to register driver under, used in template references.
     * @param ISugarStorage $driver Driver object to register.
     * @return bool true on success
     */
    public function addStorage ($name, ISugarStorage $driver) {
        $this->storage [$name]= $driver;
        return true;
    }

    /**
     * Escape the input string according to the current value of {@link Sugar::$charset}.
     *
     * @param string $output String to escape.
     * @return string Escaped output.
     */
    public function escape ($output) {
        // do not escape for raw values - just return text
        if (is_a($output, 'SugarEscaped'))
            return $output->getText();

        // perform proper escaping for current mode
        switch ($this->output) {
            case SUGAR_OUTPUT_HTML:
            case SUGAR_OUTPUT_XHTML:
                return htmlentities($output, ENT_QUOTES, $this->charset);
            case SUGAR_OUTPUT_XML:
                return htmlspecialchars($output, ENT_QUOTES, $this->charset);
            case SUGAR_OUTPUT_TEXT:
            default:
                return $output;
        }
    }

    /**
     * Process a {@link SugarException} according to the current value of {@link Sugar::$errors}.
     *
     * @param SugarException $e Exception to process.
     */
    public function handleError (SugarException $e) {
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

    /**
     * Execute Sugar bytecode.
     *
     * @param array $data Bytecode.
     * @return mixed Return value of bytecode.
     */
    private function execute (array $data) {
        // create new domain
        $this->vars []= array();

        try {
            // load runtime
            require_once SUGAR_ROOTDIR.'/Sugar/Runtime.php';

            // execute bytecode
            $rs = SugarRuntime::execute($this, $data['bytecode']);

            // cleanup
            array_pop($this->vars);
            return $rs;
        } catch (Exception $e) {
            // cleanup
            array_pop($this->vars);
            throw $e;
        }
    }

    /**
     * Load the requested template, compile it if necessary, and then
     * execute the bytecode.
     *
     * @param SugarRed $ref The template to load.
     */
    private function loadExecute (SugarRef $ref) {
        // check template exists, and remember stamp
        $sstamp = $ref->storage->stamp($ref);
        if ($sstamp === false)
            throw new SugarException('template not found: '.$ref->full);

        // if debug is off and the stamp is good, load compiled version
        $cstamp = $this->cache->stamp($ref, SUGAR_CACHE_TPL);
        if (!$this->debug && $cstamp > $sstamp) {
            $data = $this->cache->load($ref, SUGAR_CACHE_TPL);
            // if version checks out, run it
            if ($data['version'] == SUGAR_VERSION) {
                $this->execute($data);
                return;
            }
        }

        // load compiler
        require_once SUGAR_ROOTDIR.'/Sugar/Parser.php';

        // compile
        $source = $ref->storage->load($ref);
        $parser = new SugarParser($this);
        $data = $parser->compile($source, $ref->storage->path($ref));
        $parser = null;

        // store
        $this->cache->store($ref, SUGAR_CACHE_TPL, $data);

        // execute
        $this->execute($data);
    }

    /**
     * Load, compile, and display the requested template.
     *
     * @param string $file Template to display.
     * @return bool true on success.
     */
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

    /**
     * Check if a given template has a valid HTML cache.  If an HTML cache
     * already exists, applications can avoid expensive database queries
     * and other operations necessary to fill in template data.
     *
     * @param string $file File to check.
     * @param string $cacheId Optional cache identifier.
     * @return bool True if a valid HTML cache exists for the file.
     */
    function isCached ($file, $cacheId=null) {
        // validate name
        $ref = SugarRef::create($file, $this, $cacheId);
        if ($ref === false)
            throw new SugarException('illegal template name: '.$file);

        return !$this->debug && $this->cache->exists($ref, SUGAR_CACHE_HTML);
    }

    /**
     * Load, compile, and display a template, caching the result.
     *
     * @param string $file Template to display.
     * @param string $cacheId Optinal cache identifier.
     * @return bool true on success.
     */
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

            // if cache exists and is up-to-date and debug is off, load cache
            if (!$this->debug && $cstamp > $stamp) {
                $data = $this->cache->load($ref, SUGAR_CACHE_HTML);
                // if it is the right version, run it and return
                if ($data['version'] == SUGAR_VERSION) {
                    $this->execute($data);
                    return true;
                }
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

    /**
     * Compile and display the template source code given as a string.
     *
     * @param string $source Template code to display.
     * @return bool true on success.
     */
    function displayString ($source) {
        try {
            // load compiler
            require_once SUGAR_ROOTDIR.'/Sugar/Parser.php';

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

    /**
     * Fetch the source code for a template from the storage driver.
     *
     * @param string $file Template to lookup.
     * @return string Template's source code.
     */
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
