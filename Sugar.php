<?php
require_once dirname(__FILE__).'/Sugar/Exception.php';
require_once dirname(__FILE__).'/Sugar/Parser.php';
require_once dirname(__FILE__).'/Sugar/Storage.php';
require_once dirname(__FILE__).'/Sugar/Tokenizer.php';
require_once dirname(__FILE__).'/Sugar/Runtime.php';

// function registration flags
define('SUGAR_FUNC_SIMPLE', 1);
define('SUGAR_FUNC_NO_CACHE', 2);
define('SUGAR_FUNC_SUPPRESS_RETURN', 4);

class Sugar {
    private $parser = null;
    private $runtime = null;

    public $storage = null;
    public $caching = true;
    public $methods = false;

    public $templateDir = './templates';

    function __construct () {
        $this->storage = new SugarFileStorage($this);
        $this->parser = new SugarParser($this);
        $this->runtime = new SugarRuntime($this);
    }

    private function execute ($code) {
        return $this->runtime->execute($code);
    }

    // set a variable
    function set ($name, $value) {
        $name = strtolower($name);
        $this->runtime->vars [$name]= $value;
    }

    // register a function; second parameter is optional real name
    function register ($name, $invoke=false, $flags=0) {
        if ($invoke === false)
            $invoke = $name;
        $this->runtime->funcs [strtolower($name)]= array($invoke, $flags);
    }

    // compile and display given source
    function display ($file) {
        $data = $this->storage->load($file);
        if (is_string($data)) {
            $data = $this->parser->compile($data, $this->storage->getPath($file));
            if ($this->caching)
                $this->storage->store($file, $data);
        }
        $this->execute($data);
    }

    // compile and display given source
    function displayString ($source) {
        $bc = $this->parser->compile($source);
        $this->execute($bc);
    }

    // get the source code for a file
    function getSource ($file) {
        return $this->storage->getSource($file);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
