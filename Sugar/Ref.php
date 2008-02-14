<?php
/**
 * PHP-Sugar Template Engine
 *
 * Copyright (c) 2008  AwesomePlay Productions, Inc. and
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
 * @copyright 2008 AwesomePlay Productions, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

/**
 * References a Sugar file, complete with storage driver and cache ID.
 *
 * @package Sugar
 * @subpackage Drivers
 */
class SugarRef {
    /**
     * Full user-given file reference.
     *
     * @var string $full
     */
    public $full;

    /**
     * Storage driver name.
     *
     * @var string $storageName
     */
	public $storageName;

    /**
     * Storage driver for this reference.
     *
     * @var ISugarStorage $storage
     */
	public $storage;

    /**
     * File name.
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
     * Unique identifier.
     *
     * @var string $uid
     */
	public $uid;

    /**
     * Public constructor.  Parses the user-provided template path
     * and returns a SugarReg object.  This is used internally by
     * Sugar.
     *
     * @param string $path Path.
     * @param Sugar $sugar Sugar instance.
     * @param string $cacheId Optional cache ID.
     * @return SugarRef
     */
	static function create ($path, Sugar $sugar, $cacheId = null) {
		$storage = $sugar->defaultStorage;
		$name = $path;

		// clean cacheId
		if ($cacheId !== null)
			$cacheId = preg_replace('/[^A-Za-z0-9_.-]+/', '', $cacheId);

		// parse out storage
		if (preg_match('/^(\w+):(.*)$/', $path, $ar)) {
			$storage = $ar[1];
			$name = $ar[2];

			// invalid storage type
			if (!isset($sugar->storage[$storage]))
				return false;
		} else {
			$name = $path;
		}

        // strip optional .tpl trailing bit
        $name = preg_replace('/[.]tpl$/', '', $name);

		// validate name
		if (!preg_match(';/?[A-Za-z0-9_.-]+(/+[A-Za-z0-9_.-]+)*;', $name))
			return false;

		// return new reg
		return new SugarRef($path, $storage, $sugar->storage[$storage], $name, $cacheId);
	}

    /**
     * Constructor.
     *
     * @param string $full User-given path.
     * @param string $storageName Name of the storage driver in the path.
     * @param ISugarStorage $storage Storage driver.
     * @param string $name The file name of the path.
     * @param string $cacheId The cache ID for the reference.
     */
	private function __construct ($full, $storageName, ISugarStorage $storage, $name, $cacheId) {
        $this->full = $full;
		$this->storageName = $storageName;
		$this->storage = $storage;
		$this->name = $name;
		$this->cacheId = $cacheId;
        $this->uid = $full.';'.$cacheId;
	}
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
