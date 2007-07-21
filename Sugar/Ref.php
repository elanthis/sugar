<?php
/****************************************************************************
PHP-Sugar
Copyright (c) 2007  AwesomePlay Productions, Inc. and
contributors.  All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

 * Redistributions of source code must retain the above copyright notice,
   this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in the
   documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
ARE DISCLAIMED. IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
DAMAGE.
****************************************************************************/

class SugarRef {
    public $full;
	public $storageName;
	public $storage;
	public $name;
	public $cacheId;

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

	private function __construct ($full, $storageName, ISugarStorage $storage, $name, $cacheId) {
        $this->full = $full;
		$this->storageName = $storageName;
		$this->storage = $storage;
		$this->name = $name;
		$this->cacheId = $cacheId;
	}
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
