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
 * @version    Release: 0.83
 * @link       http://php-sugar.net
 */
class Sugar_Template
{
    /**
     * Name of the template as given by the user.
     *
     * @var string $name
     */
    public $name;

    /**
     * Storage driver for this reference.
     *
     * @var Sugar_StorageDriver $storage
     */
    public $storage;

    /**
     * Storage driver handle.
     *
     * @var mixed $handle 
     */
    public $handle;

    /**
     * Cache identifier.
     *
     * @var string $cacheId
     */
    public $cacheId;

    /**
     * Public constructor.  Parses the user-provided template path
     * and returns a SugarReg object.  This is used internally by
     * Sugar.
     *
     * @param Sugar  $sugar   Sugar instance.
     * @param string $path    Path.
     * @param string $cacheId Optional cache ID.
     *
     * @return Sugar_Template
     */
    public static function create(Sugar $sugar, $name, $cacheId = null)
    {
        // parse out storage driver name
        if (($pos = strpos($name, ':')) !== FALSE) {
            $storageName = substr($name, 0, $pos);
            $baseName = substr($name, $pos + 1);

            // check for invalid storage type
            if (!isset($sugar->storage[$storageName])) {
                return false;
            }
        } else {
            $storageName = $sugar->defaultStorage;
            $baseName = $name;
        }

        // load driver, and check for handler
        $storage = $sugar->storage[$storageName];
        $handle = $storage->getHandle($baseName);
        if ($handle === false) {
            return false;
        }

        // return new template object
        return new self($sugar, $storage, $handle, $name, $cacheId);
    }

    /**
     * Constructor.
     *
     * @param Sugar               $sugar       Sugar object.
     * @param Sugar_StorageDriver $storage     Storage driver.
     * @param mixed               $handle      Storage driver handle.
     * @param string              $name        Name of template requested by user.
     * @param string              $cacheId     The cache ID for the reference.
     */
    private function __construct(Sugar $sugar, Sugar_StorageDriver $storage,
    $handle, $name, $cacheId) {
        $this->sugar = $sugar;
        $this->storage = $storage;
        $this->handle = $handle;
        $this->name = $name;
        $this->cacheId = $cacheId;
    }

    /**
     * Get the last-modified timestamp of the template.
     *
     * @return int Last-modified timestamp.
     */
    public function getLastModified()
    {
        return $this->storage->getLastModified($this->handle);
    }

    /**
     * Get the source code of the template.
     *
     * @return string Source code of the template.
     */
    public function getSource()
    {
        return $this->storage->getSource($this->handle);
    }

    /**
     * Get a user-friendly name for the template
     *
     * @return string User-friendly template name.
     */
    public function getName()
    {
        return $this->storage->getName($this->handle, $this->name);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
