<?php
/**
 * Sugar (PHP Template Engine)
 *
 * This file includes the core framework for Sugar, and auto-
 * includes all necessary sub-modules.
 *
 * PHP version 5
 *
 * LICENSE:
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Template
 * @package   Sugar
 * @author    Sean Middleditch <sean@mojodo.com>
 * @copyright 2008-2009 Mojodo, Inc. and contributors
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @version   SVN: $Id$
 * @link      http://php-sugar.net
 */

/**
 * Directory in which Sugar is installed.  Used for including
 * additional Sugar source files.
 * @global string Location of core Sugar package files.
 * @internal
 */
$GLOBALS['__sugar_rootdir'] = dirname(__FILE__);

/**#@+
 * Core includes.
 */
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/Exception.php';
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/Ref.php';
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/StorageDriver.php';
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/CacheDriver.php';
/**#@-*/

/**#@+
 * Drivers.
 */
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/Storage/File.php';
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/Cache/File.php';
/**#@-*/

/**
 * Utility routines.
 */
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/Util.php';

/**
 * Sugar Standard Library.
 */
require_once $GLOBALS['__sugar_rootdir'].'/Sugar/Stdlib.php';

/**
 * Sugar core class.
 *
 * Instantiate this class to use Sugar.
 *
 * @category  Template
 * @package   Sugar
 * @author    Sean Middleditch <sean@mojodo.com>
 * @copyright 2008-2009 Mojodo, Inc. and contributors
 * @license   http://opensource.org/licenses/mit-license.php MIT
 * @version   Release: 0.83
 * @link      http://php-sugar.net
 */
class Sugar
{
    /**
     * Version of Sugar.
     */
    const VERSION = '0.83';

    /**
     * Passed to cache drivers to indicate that a compile cache is requested.
     */
    const CACHE_TPL = 'ctpl';

    /**
     * Passed to cache drivers to indicate that an HTML cache is requested.
     */
    const CACHE_HTML = 'chtml';

    /**
     * Causes all errors generated by Sugar templates to be printed to the user.
     * No indication of the error is returned to the calling script.  This is
     * the default behavior.
     */
    const ERROR_PRINT = 1;

    /**
     * Errors will be thrown as {@link Sugar_Exception} objects.
     */
    const ERROR_THROW = 2;

    /**
     * The error will be printed to the user, and then die() will be called to
     * terminate the script.
     */
    const ERROR_DIE = 3;

    /**
     * The error will be silently ignored.
     */
    const ERROR_IGNORE = 4;

    /**
     * All output will be escaped using htmlentities() with the
     * ENT_QUOTES flag set, using the {@link Sugar::$charset} setting.  This
     * is the default behavior.
     */
    const OUTPUT_HTML = 1;

    /**
     * Identical to {@link Sugar::OUTPUT_HTML}.
     */
    const OUTPUT_XHTML = 2;

    /**
     * All output will be escaped using htmlspecialchars() with the
     * ENT_QUOTES flag set, using the {@link Sugar::$charset} setting.  This
     * differs from {@link Sugar::OUTPUT_HTML} as only <, >, ", ', and & will
     * escaped.
     */
    const OUTPUT_XML = 3;

    /**
     * Disables all output escaping.
     */
    const OUTPUT_TEXT = 4;

    /**
     * Stack of variable sets.  Each template invocation creates a new
     * entry on the stack, ensuring that templates cannot subvert the
     * environment of their caller.
     *
     * @var array
     */
    private $_vars = array(array());

    /**
     * Map of all registered functions.  The key is the function name,
     * and the value is an array containing the callback and function
     * flags.
     */
    private $_functions = array();

    /**
     * Map of all registered modifiers.  The key is the modifier name,
     * and the value is the callback.
     */
    private $_modifiers = array();

    /**
     * Cache of files loaded into memory.
     */
    private $_files = array();

    /**
     * A map of storage drivers.  The key is the storage driver name,
     * and the value is the storage driver object.
     */
    public $storage = array();

    /**
     * Cache management.  Used internally.
     *
     * @var Sugar_CacheHandler
     */
    public $cacheHandler = null;

    /**
     * This is the cache driver to use for storing bytecode and HTML caches.
     * This is initialized to the {@link Sugar_Cache_File} driver by default.
     *
     * @var Sugar_CacheDriver
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
     * This is the error handling method Sugar should use.  By default,
     * errors are echoed to the screen and no exceptions are thrown.  Set
     * this to one of the following:
     * - {@link Sugar::ERROR_THROW}: throw Sugar_Exception objects
     * - {@link Sugar::ERROR_PRINT}: print an error message (default)
     * - {@link Sugar::ERROR_DIE}: terminate the script
     * - {@link Sugar::ERROR_IGNORE}: do nothing
     *
     * @var int
     */
    public $errors;

    /**
     * This is the output escaping method to be used.  This is necessary
     * for many formats, such as XML and HTML, to ensure that special
     * are escaped properly.
     * - {@link Sugar::OUTPUT_HTML}: escape HTML special characters (default)
     * - {@link Sugar::OUTPUT_XHTML}: equivalent to self::OUTPUT_HTML
     * - {@link Sugar::OUTPUT_XML}: escapes XML special characters
     * - {@link Sugar::OUTPUT_TEXT}: no escaping is performed
     *
     * @var int
     */
    public $output;

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
     * Directory to search for plugins. 
     */
    public $pluginDir = './plugins';

    /**
     * Character set that output should be in.
     *
     * @var string
     */
    public $charset = 'ISO-8859-1';

    /**
     * Opening delimiter character.
     *
     * @var string
     */
    public $delimStart = '{%';

    /**
     * Closing delimiter character.
     *
     * @var string
     */
    public $delimEnd = '%}';

    /**
     * Callback for checking method access.
     *
     * @var callback
     */
    public $method_acl;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->storage ['file']= new Sugar_Storage_File($this);
        $this->cache = new Sugar_Cache_File($this);
        $this->errors = self::ERROR_PRINT;
        $this->output = self::OUTPUT_HTML;
    }

    /**
     * Set a new variable to be available within templates.
     *
     * @param string $name  The variable's name.
     * @param mixed  $value The variable's value.
     *
     * @return bool true on success
     */
    public function set($name, $value)
    {
        $name = strtolower($name);
        $this->_vars[count($this->_vars)-1] [$name]= $value;
        return true;
    }

    /**
     * Registers a new function to be available within templates.
     *
     * @param string   $name   The name to register the function under.
     * @param callback $invoke Optional PHP callback; if null, the $name
     *                         parameter is used as the callback.
     * @param boo      $cache  Whether the function is cacheable.
     * @param boo      $escape Whether the function output should be escaped.
     *
     * @return bool true on success
     */
    public function addFunction($name, $invoke = null, $cache = true, $escape = true)
    {
        if (!$invoke) {
            $invoke = 'sugar_function_'.strtolower($name);
        }

        $this->_functions [strtolower($name)]= array('name'=>$name,
                'invoke'=>$invoke, 'cache'=>$cache, 'escape'=>$escape);
        return true;
    }

    /**
     * Registers a new modifier to be available within templates.
     *
     * @param string   $name   The name to register the modifier under.
     * @param callback $invoke Optional PHP callback; if null, the $name
     *                         parameter is used as the callback.
     *
     * @return bool  true on success
     */
    public function addModifier($name, $invoke = null)
    {
        if (!$invoke) {
            $invoke = 'sugar_modifier_'.strtolower($name);
        }

        $this->_functions [strtolower($name)]= $invoke;
        return true;
    }

    /**
     * Looks up the current value of a variable.
     *
     * @param string $name Name of the variable to lookup.
     *
     * @return mixed
     */
    public function getVariable($name)
    {
        $name = strtolower($name);
        for ($i = count($this->_vars)-1; $i >= 0; --$i) {
            if (array_key_exists($name, $this->_vars[$i])) {
                return $this->_vars[$i][$name];
            }
        }
        return null;
    }

    /**
     * Returns an array containing the data for template function.  This
     * will first look for registered functions, then it will attempt to
     * auto-register a function using the smarty_function_foo naming
     * scheme.  Finally, it will attempt to load a function plugin.
     *
     * @param string $name Function name to lookup.
     *
     * @return array
     */
    public function getFunction($name)
    {
        $name = strtolower($name);

        // check for registered functions
        if (isset($this->_functions[$name])) {
            return $this->_functions[$name];
        }

        // try to auto-lookup the function
        $invoke = "sugar_function_$name";
        if (function_exists($invoke)) {
            return $this->_functions[$name] = array('name'=>$name,
                    'invoke'=>$invoke, 'cache'=>true, 'escape'=>true);
        }

        // attempt plugin loading
        $file = "{$this->pluginDir}/$invoke.php";
        if (file_exists($file)) {
            @include_once $file;
            if (function_exists($invoke)) {
                $this->_functions[$name] = array('name'=>$name,
                        'invoke'=>$invoke, 'cache'=>true, 'escape'=>true);
                return $this->_functions[$name];
            }
        }

        // nothing found
        return false;
    }

    /**
     * Returns the callback for a template modifier, if it exists.  This
     * will first look for registered modifiers, then it will attempt to
     * auto-register a modifier using the smarty_modifier_foo naming
     * scheme.  Finally, it will attempt to load a modifier plugin.
     *
     * @param string $name Modifier name to lookup.
     *
     * @return array
     */
    public function getModifier($name)
    {
        $name = strtolower($name);
        // check for registered modifiers
        if (isset($this->_modifiers[$name])) {
            return $this->_modifiers[$name];
        }

        // try to auto-lookup the modifier
        $invoke = "sugar_modifier_$name";
        if (function_exists($invoke)) {
            return $this->_modifiers[$name] = $invoke;
        }

        // attempt plugin loading
        $file = "{$this->pluginDir}/$invoke.php";
        if (file_exists($file)) {
            @include_once $file;
            if (function_exists($invoke)) {
                return $this->_modifiers[$name] = $invoke;
            }
        }

        // nothing found
        return false;
    }

    /**
     * Register a new storage driver.
     *
     * @param string        $name   Name to register driver under, used in
     *                              template references.
     * @param Sugar_StorageDriver $driver Driver object to register.
     *
     * @return bool true on success
     */
    public function addStorage($name, Sugar_StorageDriver $driver)
    {
        $this->storage [$name]= $driver;
        return true;
    }

    /**
     * Change the current delimiters.
     *
     * @param string $start Starting delimiter (default '{%')
     * @param string $end   Ending delimiter (default '%}')
     *
     * @return bool true on success
     */
    public function setDelimiter($start, $end)
    {
        $this->delimStart = $start;
        $this->delimEnd = $end;
        return true;
    }

    /**
     * Escape the input string according to the current value of
     * {@link Sugar::$charset}.
     *
     * @param string $string String to escape.
     *
     * @return string Escaped output.
     */
    public function escape($string)
    {
        // make sure this is a valid string
        $string = strval($string);

        // perform proper escaping for current mode
        switch ($this->output) {
        case self::OUTPUT_HTML:
            return htmlentities($string, ENT_COMPAT, $this->charset);
        case self::OUTPUT_XHTML:
        case self::OUTPUT_XML:
            return htmlspecialchars($string, ENT_QUOTES, $this->charset);
        case self::OUTPUT_TEXT:
        default:
            return $string;
        }
    }

    /**
     * Process an exception according to the current value of {@link Sugar::$errors}.
     *
     * @param Exception $e Exception to process.
     *
     * @return bool true on success
     */
    public function handleError(Exception $e)
    {
        // if in throw mode, re-throw the exception
        if ($this->errors == self::ERROR_THROW) {
            throw $e;
        }

        // if in ignore mode, just return
        if ($this->errors == self::ERROR_IGNORE) {
            return true;
        }

        // print the error
        echo "\n[[ ", $this->escape(get_class($e)), ': ',
                $this->escape($e->getMessage()), " ]]\n";

        // die if in die mode
        if ($this->errors == self::ERROR_DIE) {
            die();
        }

        return true;
    }

    /**
     * Execute Sugar bytecode.
     *
     * @param array $data Bytecode.
     * @param array $vars Additional vars to set during execution.
     *
     * @return mixed Return value of bytecode.
     */
    private function _execute(array $data, $vars = null)
    {
        // create new domain -- with vars, if set
        if (is_array($vars)) {
            $this->_vars []= $vars; 
        } else {
            $this->_vars []= array();
        }

        try {
            /**
             * Runtime.
             */
            include_once $GLOBALS['__sugar_rootdir'].'/Sugar/Runtime.php';

            // execute bytecode
            $rs = Sugar_Runtime_Execute($this, $data['bytecode'], $data['sections']);

            // cleanup
            array_pop($this->_vars);
            return $rs;
        } catch (Exception $e) {
            // cleanup
            array_pop($this->_vars);
            throw $e;
        }
    }

    /**
     * Load the requested template and compile it if necessary.
     *
     * @param Sugar_Ref $ref    The template to load.
     *
     * @return mixed Compiled bytecode information.
     * @throws Sugar_Exception_Usage when the template cannot be found.
     */
    private function _loadCompile(Sugar_Ref $ref) {
        // check template exists, and remember stamp
        $sstamp = $ref->storage->stamp($ref);
        if ($sstamp === false) {
            throw new Sugar_Exception_Usage('template not found: '.$ref->full);
        }

        // cache file ref
        if ($this->cacheHandler) {
            $this->cacheHandler->addRef($ref);
        }

        // if debug is off and the stamp is good, load compiled version
        $cstamp = $this->cache->stamp($ref, self::CACHE_TPL);
        if (!$this->debug && $cstamp !== false && $cstamp > $sstamp) {
            $data = $this->cache->load($ref, self::CACHE_TPL);
            // if version checks out, run it
            if ($data !== false && $data['version'] === self::VERSION) {
                return $data;
            }
        }

        /**
         * Compiler.
         */
        include_once $GLOBALS['__sugar_rootdir'].'/Sugar/Grammar.php';

        // compile
        $source = $ref->storage->load($ref);
        if ($source === false) {
            throw new Sugar_Exception_Usage('template not found: '.$ref->full);
        }
        $parser = new Sugar_Grammar($this);
        $data = $parser->compile($source, $ref->storage->path($ref));
        $parser = null;

        // store
        $this->cache->store($ref, self::CACHE_TPL, $data);

        return $data;
    }

    /**
     * Load the requested template, compile it if necessary, and then
     * execute the bytecode.
     *
     * @param Sugar_Ref $ref    The template to load.
     * @param array    $vars   Additional vars to set during execution.
     * @param string   $layout Template to use as a layout wrapper.
     *
     * @return mixed Return value of bytecode.
     * @throws Sugar_Exception_Usage when the template cannot be found.
     */
    private function _loadExecute(Sugar_Ref $ref, $vars, $layout)
    {
        // load compiled template
        $data = $this->_loadCompile($ref);

        // if we have no layout, execute the template as-is
        if (!$layout) {
            return $this->_execute($data, $vars);
        }

        // load compiled layout
        $layoutRef = Sugar_Ref::Create($layout, $this, $ref->cacheId, null);
        $layoutData = $this->_loadCompile($layoutRef);

        // merge layout with page template
        $merged = $layoutData;
        $merged['sections'] = array_merge($layoutData['sections'], $data['sections']);

        // set page main bytecode as content section if and only if
        // the page template did not define its own explicit content
        // section.
        if (!isset($data['sections']['content'])) {
            $merged['sections']['content'] = $data['bytecode'];
        }

        // execute merged template
        return $this->_execute($merged, $vars);
    }
    
    /**
     * Attempt to load an HTML cached file.  Will return false if
     * the cached file does not exist or if the cached file is out
     * of date.
     *
     * @param Sugar_Ref $ref Cache reference.
     *
     * @return false|array Cache data on success, false on error.
     */
    private function _loadCache(Sugar_Ref $ref)
    {
        // if the file is already loaded, use that
        if (isset($this->_files[$ref->uid])) {
            return $this->_files[$ref->uid];
        }

        // get the cache's stamp, and fail if it can't be found
        $cstamp = $this->cache->stamp($ref, self::CACHE_HTML);
        if ($cstamp === false) {
            return false;
        }

        // fail if the cache is too old
        if ($cstamp < time() - $this->cacheLimit) {
            return false;
        }

        // load the cache data, fail if loading fails or the
        // version doesn't match
        $data = $this->cache->load($ref, self::CACHE_HTML);
        if ($data === false || $data['version'] !== self::VERSION) {
            return false;
        }

        // compare stamps with the included references
        foreach ($data['refs'] as $file) {
            // try to reference the file; ignore failures
            $iref = Sugar_Ref::create($file, $this);
            if (!$iref) {
                continue;
            }

            // get the stamp of the reference; ignore failures
            $stamp = $iref->storage->stamp($iref);
            if ($stamp === false) {
                continue;
            }

            // if the stamp is newer than the cache stamp, fail
            if ($cstamp < $stamp) {
                return false;
            }
        }

        // cache this file
        $this->_files[$ref->uid] = $data;

        // everything has checked out, the cache is valid
        return $data;
    }

    /**
     * Load, compile, and display the requested template.
     *
     * @param string $file   Template to display.
     * @param array  $vars   Additional vars to set during execution.
     * @Param string $layout Template to use for layout wrapper.
     *
     * @return bool true on success.
     * @throws Sugar_Exception_Usage when the template name is invalid or
     * the template cannot be found.
     */
    public function display($file, $vars = null, $layout = null)
    {
        // validate name
        $ref = Sugar_Ref::create($file, $this, null, $layout);
        if ($ref === false) {
            throw new Sugar_Exception_Usage('illegal template name: '.$file);
        }

        // ensure template exists
        if ($ref->storage->stamp($ref) === false) {
            throw new Sugar_Exception_Usage('template not found: '.$ref->full);
        }

        // load and run
        try {
            $this->_loadExecute($ref, $vars, $layout);
            return true;
        } catch (Sugar_Exception $e) {
            $this->handleError($e);
            return false;
        }

        return true;
    }

    /**
     * Displays a template using {@link Sugar::display}, but returns
     * the result as a string instead of displaying it to the user.
     *
     * @param string $file   Template to process.
     * @param array  $vars   Additional vars to set during execution.
     * @param string $layout Template to use for layout wrapper.
     *
     * @return string Template output.
     */
    public function fetch($file, $vars = null, $layout = null)
    {
        ob_start();
        $this->display($file, $vars, $layout);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    /**
     * Check if a given template has a valid HTML cache.  If an HTML cache
     * already exists, applications can avoid expensive database queries
     * and other operations necessary to fill in template data.
     *
     * @param string $file    File to check.
     * @param string $cacheId Optional cache identifier.
     * @param array  $vars    Additional vars to set during execution.
     * @param string $layout  Template to use for layout wrapper.
     *
     * @return bool True if a valid HTML cache exists for the file.
     * @throws Sugar_Exception_Usage when the template name is invalid.
     */
    function isCached($file, $cacheId = null, $vars = null, $layout = null)
    {
        // debug always disabled caching
        if ($this->debug) {
            return false;
        }

        // validate name
        $ref = Sugar_Ref::create($file, $this, $cacheId, $layout);
        if ($ref === false) {
            throw new Sugar_Exception_Usage('illegal template name: '.$file);
        }

        // if the cache can be loaded, it is valid
        return $this->_loadCache($ref) !== false;
    }

    /**
     * Erases the HTML cache for a template if it exists.
     *
     * @param string $file    File to check.
     * @param string $cacheId Optional cache identifier.
     * @param string $layout  Optional layout template.
     *
     * @throws Sugar_Exception_Usage when the template name is invalid.
     */
    function uncache($file, $cacheId = null, $layout = null)
    {
        // validate name
        $ref = Sugar_Ref::create($file, $this, $cacheId, $layout);
        if ($ref === false) {
            throw new Sugar_Exception_Usage('illegal template name: '.$file);
        }

        // erase the cache entry
        $this->cache->erase($ref, self::CACHE_HTML);
    }

    /**
     * Clear all HTML cache files.
     */
    function clearCache()
    {
        $this->cache->clear();
    }

    /**
     * Load, compile, and display a template, caching the result.
     *
     * @param string $file    Template to display.
     * @param string $cacheId Optinal cache identifier.
     * @param array  $vars    Additional vars to set during execution.
     * @param string $layout  Template to use for layout wrapper.
     *
     * @return bool true on success.
     * @throws Sugar_Exception_Usage when the template name is invalid.
     */
    function displayCache($file, $cacheId = null, $vars = null, $layout = null)
    {
        // validate name
        $ref = Sugar_Ref::create($file, $this, $cacheId, $layout);
        if ($ref === false) {
            throw new Sugar_Exception_Usage('illegal template name: '.$file);
        }

        try {
            // if cache exists and is up-to-date and debug is off, load cache
            $data = $this->_loadCache($ref);
            if (!$this->debug && $data !== false) {
                $this->_execute($data, $vars);
                return true;
            }

            // if we are running inside an existing cache handler,
            // execute and add to current cache
            if ($this->cacheHandler) {
                $this->_loadExecute($ref, $vars, $layout);
                return true;
            }

            /**
             * Cache handler.
             */
            include_once $GLOBALS['__sugar_rootdir'].'/Sugar/CacheHandler.php';

            // create cache
            $this->cacheHandler = new Sugar_CacheHandler($this);
            $this->_loadExecute($ref, $vars, $layout);
            $cache = $this->cacheHandler->getOutput();
            $this->cacheHandler = null;

            // attempt to save cache
            $this->cache->store($ref, self::CACHE_HTML, $cache);

            // display cache
            $this->_execute($cache, $vars);
            return true;
        } catch (Sugar_Exception $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * Displays a cached template using {@link Sugar::displayCache}, but
     * returns the result as a string instead of displaying it to the user.
     *
     * @param string $file    Template to process.
     * @param string $cacheId Optional cache identifier.
     * @param array  $vars    Additional vars to set during execution.
     * @param string $layout  Template to use for layout wrapper.
     *
     * @return string Template output.
     */
    public function fetchCache($file, $cacheId = null, $vars = null,
    $layout = null)
    {
        ob_start();
        $this->displayCache($file, $cacheId, $vars, $layout);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    /**
     * Compile and display the template source code given as a string.
     *
     * It is recommended that this method be avoided in real applications,
     * as it can have drastic performance consequences.
     *
     * @param string $source Template code to display.
     * @param array  $vars   Additional vars to set during execution.
     *
     * @return bool true on success.
     */
    function displayString($source, $vars = null)
    {
        try {
            /**
             * Compiler.
             */
            include_once $GLOBALS['__sugar_rootdir'].'/Sugar/Grammar.php';

            // compile
            $parser = new Sugar_Grammar($this);
            $data = $parser->compile($source);
            $parser = null;

            // run
            $this->_execute($data, $vars);
            
            return true;
        } catch (Sugar_Exception $e) {
            $this->handleError($e);
            return false;
        }
    }

    /**
     * Processes template source using {@link Sugar::displayString}, but
     * returns the result as a string instead of displaying it to the user.
     *
     * It is recommended that this method be avoided in real applications,
     * as it can have drastic performance consequences.
     *
     * @param string $source Template code to process.
     * @param array  $vars   Additional vars to set during execution.
     *
     * @return string Template output.
     */
    public function fetchString($source, $vars = null)
    {
        ob_start();
        $this->displayString($source, $vars);
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    /**
     * Fetch the source code for a template from the storage driver.
     *
     * @param string $file Template to lookup.
     *
     * @return string Template's source code.
     * @throws Sugar_Exception_Usage when the template name is invalid.
     */
    function getSource($file)
    {
        // validate name
        $ref = Sugar_Ref::create($file, $this);
        if ($ref === false) {
            throw new Sugar_Exception_Usage('illegal template name: '.$file);
        }

        // fetch source
        return $ref->storage->load($ref);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
