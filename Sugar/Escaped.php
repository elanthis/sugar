<?php
class SugarEscaped {
	private $text;

	public function __construct ($text) {
		$this->text = $text;
	}

	public function getText () {
		return $this->text;
	}

	public function __toString () {
		return $this->text;
	}
}
