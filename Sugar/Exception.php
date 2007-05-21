<?php
class SugarException extends Exception {
	public function __toString () {
		return 'Sugar Core Error: '.$this->getMessage();
	}
}

class SugarParseException extends SugarException {
	var $file = '<input>';
	var $line = 1;
	var $msg;

	public function __construct ($file, $line, $msg) {
		parent::__construct($msg);
		$this->file = $file;
		$this->line = $line;
	}

	public function __toString () {
		return 'Sugar Parse Error: '.$this->file.','.$this->line.': '.$this->getMessage();
	}
}

class SugarRuntimeException extends SugarException {
	public function __toString () {
		return 'Sugar Runtime Error: '.$this->getMessage();
	}
}
?>
