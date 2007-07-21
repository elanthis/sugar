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

class SugarCacheFile implements ISugarCache {
    private $sugar;
    private $useJson;

    public function __construct (&$sugar) {
        $this->sugar =& $sugar;
        $this->useJson = function_exists('json_encode');
    }

    private function makePath (SugarRef $ref, $type) {
        $path = $this->sugar->cacheDir.'/';
        $path .= md5($ref->storageName .$ref->name . $ref->cacheId);
        $path .= ',' . $ref->storageName . ',' . str_replace('/', '%', $ref->name);
        if ($ref->cacheId !== null)
            $path .= ',' . preg_replace('/[^A-Za-z0-9._-]+/', '', $ref->cacheId);
        $path .= ',' . $type;
        return $path;
    }

    public function stamp (SugarRef $ref, $type) {
        $path = $this->makePath($ref, $type);

        // check exists, return stamp
        if (file_exists($path) && is_file($path) && is_readable($path) && time()-filemtime($path)<=$this->sugar->cacheLimit)
            return filemtime($path);
        else
            return false;
    }

    public function load (SugarRef $ref, $type) {
        $path = $this->makePath($ref, $type);
    
        // must exist, be readable, and not be older than $cacheLimit seconds
        if (file_exists($path) && is_file($path) && is_readable($path) && time()-filemtime($path)<=$this->sugar->cacheLimit) {
            // load, deserialize
            $data = file_get_contents($path);
            // decode 
            if (substr($data, 0, 1) == '[') // indicates json
                $data = json_decode($data);
            else // must be PHP serialized
                $data = unserialize($data);

            // execute
            return $data;
        } else 
            return false;

        return false;
    }

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
        if ($this->useJson)
            $data = json_encode($data);
        else
            $data = serialize($data);
        file_put_contents($path, $data);
        return true; 
    }

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
