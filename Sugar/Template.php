<?php
/**
 * Template instance class.
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
 * @subpackage Template
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2008-2009 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    SVN: $Id$
 * @link       http://php-sugar.net
 */

/**
 * Template instance object.
 *
 * Encapsulates all operations to be performed for a particular template.
 *
 * @category   Template
 * @package    Sugar
 * @subpackage Template
 * @author     Sean Middleditch <sean@mojodo.com>
 * @copyright  2008-2010 Mojodo, Inc. and contributors
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @version    Release: 0.84
 * @link       http://php-sugar.net
 */
class Sugar_Template
{
    /**
     * Our Sugar instance
     *
     * @var Sugar $sugar
     */
    private $_sugar;

    /**
     * Name of the template as given by the user.
     *
     * @var string $name
     */
    public $name;

    /**
     * Cache identifier.
     *
     * @var string $cacheId
     */
    public $cacheId;

    /**
     * Storage driver for this reference.
     *
     * @var Sugar_Storage $storage
     */
    private $_storage;

    /**
     * Storage driver handle.
     *
     * @var mixed $_handle 
     */
    private $_handle;

    /**
     * Local variable data
     *
     * @var Sugar_Data $_data
     */
    private $_data;

    /**
     * HTML cache data.
     *
     * If an array, it's a valid cache.  If null, we haven't checked.
     * If false, it's known to be out of date.
     *
     * @var mixed $_htmlCache
     */
    private $_htmlCache = null;

    /**
     * Compiled template cache.
     *
     * @var mixed $_compiled
     */
    private $_compiled = null;

    /**
     * Optional inherited template, overrides template specified inherited
     * template.
     *
     * @var string $_inherit
     */
    private $_inherit = null;

    /**
     * Constructor.
     *
     * @param Sugar         $sugar       Sugar object.
     * @param Sugar_Storage $storage     Storage driver.
     * @param mixed         $handle      Storage driver handle.
     * @param string        $name        Name of template requested by user.
     * @param string        $cacheId     The cache ID for the reference.
     */
    public function __construct(Sugar $sugar, Sugar_Storage $storage,
    $handle, $name, $cacheId) {
        $this->_sugar = $sugar;
        $this->_storage = $storage;
        $this->_handle = $handle;
        $this->name = $name;
        $this->cacheId = $cacheId;

        $this->_data = new Sugar_Data($sugar->getGlobals(), array());
    }

    /**
     * Get the last-modified timestamp of the template.
     *
     * @return int Last-modified timestamp.
     */
    public function getLastModified()
    {
        return $this->_storage->getLastModified($this->_handle);
    }

    /**
     * Get the source code of the template.
     *
     * @return string Source code of the template.
     */
    public function getSource()
    {
        return $this->_storage->getSource($this->_handle);
    }

    /**
     * Get a user-friendly name for the template
     *
     * @return string User-friendly template name.
     */
    public function getFriendlyName()
    {
        return $this->_storage->getFriendlyName($this->_handle, $this->name);
    }

    /**
     * Get the template's local variable data
     *
     * @return Sugar_Data
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Set the inherited template, which overrides any inherited
     * template specified in the template source.
     *
     * @param string $file Template to inherit from
     */
    public function setInherit($file)
    {
        $this->_inherit = $file;
    }

    /**
     * Get the inherited template override
     *
     * @return mixed
     */
    public function getInherit()
    {
        return $this->_inherit;
    }

    /**
     * Check if the template has a valid and completely up-to-date ache.
     *
     * This will check the cache status of included templates as well.
     *
     * @return bool True for a valid cache, false if missing or outdated.
     */
    public function isCached()
    {
        $code = $this->_sugar->getLoader()->getCached($this);
        return $code !== false;
    }

    /**
     * Uncache this template
     */
    public function uncache()
    {
        $this->_sugar->cache->erase($this, self::CACHE_HTML);
    }

    /**
     * Helper to set a variable in the template's local data
     *
     * @param string $name  Name of variable to set
     * @param mixed  $value Value of variable
     */
    public function set($name, $value)
    {
        $this->_data->set($name, $value);
    }

    /**
     * Display the template
     *
     * @param Sugar_Data $data Optional data to use instead
     *                           of the default local data
     */
    public function display($data = null)
    {
        // use a default data set if none provided
        if (is_null($data)) {
            $data = new Sugar_Data($this->getData(), array());
        }

        // create context, execute
        $runtime = new Sugar_Runtime($this->_sugar);
        $runtime->execute($this, $data);
    }

    /**
     * Fetch template output as a string
     *
     * @param Sugar_Data $data Optional data to use instead
     *                           of the default local data
     *
     * @return string
     */
    public function fetch($data = null)
    {
        ob_start();
        try {
            $this->display($data);
            $output = ob_get_contents();
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
        ob_end_clean();
        return $output;
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
