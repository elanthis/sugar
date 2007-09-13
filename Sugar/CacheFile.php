<?php
/**
 * PHP-Sugar Template Engine
 *
 * Copyright (c) 2007  AwesomePlay Productions, Inc. and
 * contributors.  All rights reserved.
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
 * @package Sugar
 * @subpackage Drivers
 * @author Sean Middleditch <sean@awesomeplay.com>
 * @copyright 2007 AwesomePlay Productions, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

/**
 * File-based cache driver.
 *
 * Uses {@link Sugar::$cacheDir} and {$link Sugar::$cacheTime} to control
 * behavior.
 *
 * @package Sugar
 * @subpackage Drivers
 */
class SugarCacheFile implements ISugarCache {
    /**
     * Sugar instance.
     *
     * @var Sugar $sugar
     */
    private $sugar;

    /**
     * Constructor.
     *
     * @param Sugar $sugar Sugar instance.
     */
    public function __construct ($sugar) {
        $this->sugar = $sugar;
    }

    /**
     * Makes a path for teh given reference.
     *
     * @param SugarRef $ref File reference.
     * @param string $type Either 'ctpl' or 'chtml'.
     * @return string Path.
     */
    private function makePath (SugarRef $ref, $type) {
        $path = $this->sugar->cacheDir.'/';
        $path .= md5($ref->storageName .$ref->name . $ref->cacheId);
        $path .= ',' . $ref->storageName . ',' . str_replace('/', '%', $ref->name);
        if ($ref->cacheId !== null)
            $path .= ',' . preg_replace('/[^A-Za-z0-9._-]+/', '', $ref->cacheId);
        $path .= ',' . $type;
        return $path;
    }

    /**
     * Returns the timestamp.
     *
     * @param SugarRef $ref File reference.
     * @param string $type Either 'ctpl' or 'chtml'.
     * @return int Timestamp
     */
    public function stamp (SugarRef $ref, $type) {
        $path = $this->makePath($ref, $type);

        // check exists, return stamp
        if (file_exists($path) && is_file($path) && is_readable($path) && time()-filemtime($path)<=$this->sugar->cacheLimit)
            return filemtime($path);
        else
            return false;
    }

    /**
     * Returns the bytecode for the requested reference.
     *
     * @param SugarRef $ref File reference to lookup.
     * @param string $type Either 'ctpl' or 'chtml'.
     * @return array Bytecode, or false if not in the cache.
     */
    public function load (SugarRef $ref, $type) {
        $path = $this->makePath($ref, $type);
    
        // must exist, be readable, and not be older than $cacheLimit seconds
        if (file_exists($path) && is_file($path) && is_readable($path) && time()-filemtime($path)<=$this->sugar->cacheLimit) {
            // load, deserialize
            $data = file_get_contents($path);
            $data = unserialize($data);
            return $data;
        } else 
            return false;

        return false;
    }

    /**
     * Adds the bytecode to the cache.
     *
     * @param SugarRef $ref File reference to lookup.
     * @param string $type Either 'ctpl' or 'chtml'.
     * @param array $data Bytecode.
     */
    public function store (SugarRef $ref, $type, $data) {
        $path = $this->makePath($ref, $type);

        // ensure we can save the cache file
        if (!file_exists($this->sugar->cacheDir))
            throw new SugarException('cache directory does not exist: '.$this->sugar->cacheDir);
        if (!is_dir($this->sugar->cacheDir))
            throw new SugarException('cache directory is not a directory: '.$this->sugar->cacheDir);
        if (!is_writeable($this->sugar->cacheDir))
            throw new SugarException('cache directory is not writable: '.$this->sugar->cacheDir);

        // encode, save
        $data = serialize($data);
        file_put_contents($path, $data);
        return true; 
    }

    /**
     * Erases the bytecode for the requested reference.
     *
     * @param SugarRef $ref File reference for the bytecode to erase.
     * @param string $type Either 'ctpl' or 'chtml'.
     */
    public function erase (SugarRef $ref, $type) {
        $path = $this->makePath($ref, $type);

        // if the file exists and the directory is writeable, erase it
        if (file_exists($path) && is_file($path) && is_writeable($this->sugar->cacheDir)) {
            unlink($path);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Clears all caches the driver is responsible for.
     */
    public function clear () {
        // directory must exist, and be both readable and writable
        if (!file_exists($this->sugar->cacheDir) || !is_dir($this->sugar->cacheDir) || !is_writable($this->sugar->cacheDir) || !is_readable($this->sugar->cacheDir))
            return false;

        $dir = opendir($this->sugar->cacheDir);
        while ($cache = readdir($dir))
            if (preg_match('/^[^.].*[.](ctpl|chtml)$/', $cache))
                unlink($this->sugar->cacheDir.'/'.$cache);

        return true;
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
