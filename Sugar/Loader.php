<?php
/**
 * Data loading and caching frontend.
 *
 * This is the runtime engine, which takes the code compiled by SugarParser
 * and makes it go.  It handles the various operators, output escaping,
 * caching, and method/function invocation with exception safety nets.
 *
 * The design is not particularly clever or efficient, and could use a could
 * round of profiling and improvement.  Parsing only gets called once every
 * time a template is modified, but the runtime is invoked for every single
 * page display, even for cached pages.
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
 * @category   Template
 * @package    Sugar
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2008-2009 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    SVN: $Id$
 * @link       http://php-sugar.net
 * @access     private
 */

/**
 * Data loading and caching frontend.
 *
 * @category   Template
 * @package    Sugar
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2008-2009 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    SVN: $Id$
 * @link       http://php-sugar.net
 * @access     private
 */
final class Sugar_Loader {
    /**
     * Sugar instance
     *
     * @var Sugar
     */
    private $_sugar;

    /**
     * Cache handler.
     *
     * @var Sugar_Cache
     */
    private $_cache;

    /**
     * Compiled code cache
     *
     * @var array
     */
    private $_compiled = array();

    /**
     * Cache output cache
     *
     * @var array
     */
    private $_cached = array();

    /**
     * Constructor
     *
     * @param Sugar       $sugar Sugar Instance
     */
    public function __construct(Sugar $sugar)
    {
        $this->_sugar = $sugar;
    }

    /**
     * Set the HTML cache data for a particular template
     *
     * @param Sugar_Template $template
     * @param mixed          $data
     * @returns mixed Copy of $data
     */
    public function setCached(Sugar_Template $template, $data)
    {
        $this->_cached[$template->name][$template->cacheId] = $data;
        return $data;
    }
    
    /**
     * Attempt to load an HTML cached file.  Will return false if
     * the cached file does not exist or if the cached file is out
     * of date.
     *
     * @param Sugar_Template $template
     * @return false|array Cache data on success, false on error.
     */
    public function getCached(Sugar_Template $template)
    {
        // if the cache is already loaded, just return it
        if (isset($this->_cached[$template->name][$template->cacheId])) {
            return $this->_cached[$template->name][$template->cacheId];
        }

        // get the cache's stamp, and fail if it can't be found
        $cstamp = $this->_sugar->cache->getLastModified($template, Sugar::CACHE_HTML);
        if ($cstamp === false) {
            return $this->setCached($template, false);
        }

        // fail if the cache is too old
        if ($cstamp < time() - $this->_sugar->cacheLimit) {
            return $this->setCached($template, false);
        }

        // load the cache data, fail if loading fails or the
        // version doesn't match
        $data = $this->_sugar->cache->load($template, Sugar::CACHE_HTML);
        if ($data === false) {
            return $this->setCached($template, false);
        }

        // compare stamps with the included references; if any fail,
        // unmark our _cached flag so we can report back to the user
        // on a call to isCached()
        foreach ($data->getReferences() as $file) {
            // try to reference the file; ignore failures
            $inc = $this->_sugar->getTemplate($file, $template->cacheId);
            if ($inc === false) {
                continue;
            }

            // get the stamp of the reference; ignore failures
            $stamp = $inc->getLastModified();
            if ($stamp === false) {
                continue;
            }

            // if the stamp is newer than the cache stamp, fail
            if ($cstamp < $stamp) {
                return $this->setCached($template, false);
            }
        }

        // store the bytecode so we don't need to reload it
        return $this->setCached($template, $data);
    }

    /**
     * Set the compiled code data for a particular template
     *
     * @param Sugar_Template $template
     * @param mixed          $data
     * @returns mixed Copy of $data
     */
    public function setCompiled(Sugar_Template $template, $data)
    {
        $this->_compiled[$template->name] = $data;
        return $data;
    }

    /**
     * Load and compile (if necessary) the template code.
     *
     * @param Sugar_Template $template
     * @return mixed
     */
    public function getCompiled(Sugar_Template $template)
    {
        // if we already have a compiled version, don't reload
        if (isset($this->_compiled[$template->name])) {
            return $this->_compiled[$template->name];
        }

        // if debug is off and the stamp is good, load compiled version
        if (!$this->_sugar->debug) {
            $sstamp = $template->getLastModified();
            $cstamp = $this->_sugar->cache->getLastModified($template, Sugar::CACHE_TPL);
            if ($cstamp !== false && $cstamp > $sstamp) {
                $data = $this->_sugar->cache->load($template, Sugar::CACHE_TPL);
                // if version checks out, run it
                if ($data !== false && $data) {
                    return $this->setCompiled($template, $data);
                }
            }
        }

        /**
         * Compiler.
         */
        include_once $GLOBALS['__sugar_rootdir'].'/Sugar/Grammar.php';

        // compile
        $source = $template->getSource();
        if ($source === false) {
            throw new Sugar_Exception_Usage('template not found: '.$template->getFriendlyName());
        }
        $parser = new Sugar_Grammar($this->_sugar);
        $data = $parser->compile($template, $source);
        unset($parser);

        // store compiled bytecode into cache
        $this->_sugar->cache->store($template, Sugar::CACHE_TPL, $data);

        return $this->setCompiled($template, $data);
    }
}

// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
