<?php
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
