<?php
require_once dirname(__FILE__).'/Sugar/Exception.php';
require_once dirname(__FILE__).'/Sugar/Parser.php';
require_once dirname(__FILE__).'/Sugar/Storage.php';
require_once dirname(__FILE__).'/Sugar/Tokenizer.php';
require_once dirname(__FILE__).'/Sugar/Runtime.php';
require_once dirname(__FILE__).'/Sugar/Stdlib.php';

// function registration flags
define('SUGAR_FUNC_SIMPLE', 1);
define('SUGAR_FUNC_NO_CACHE', 2);
define('SUGAR_FUNC_SUPPRESS_RETURN', 4);

class Sugar {
    private $vars = array(array());
    private $funcs = array();

    public $storage = null;
    public $debug = false;
    public $methods = false;

    function __construct () {
        $this->storage = new SugarFileStorage($this);

        SugarStdlib::initialize($this);
    }

    // set a variable
    function set ($name, $value) {
        $name = strtolower($name);
        $this->vars[count($this->vars)-1] [$name]= $value;
    }

    function get ($name) {
        $name = strtolower($name);
        for ($i = count($this->vars)-1; $i >= 0; --$i)
            if (array_key_exists($name, $this->vars[$i]))
                return $this->vars[$i][$name];
        return null;
    }

    // register a function; second parameter is optional real name
    function register ($name, $invoke=false, $flags=0) {
        if ($invoke === false)
            $invoke = $name;
        $this->funcs [strtolower($name)]= array($invoke, $flags);
    }

    // return a function from the registered list
    function getFunction ($name) {
        return $this->funcs[strtolower($name)];
    }

    // execute
    private function execute (&$data) {
        $this->vars []= array();
        SugarRuntime::execute($this, $data);
        array_pop($this->vars);
    }

    // compile and display given source
    function display ($file) {
        try {
            $data = $this->storage->load($file);
            $this->execute($data);
        } catch (SugarException $e) {
            echo '<p><b>'.htmlentities($e->__toString()).'</b></p>';
            return false;
        }

        return true;
    }

    // compile and display given source
    function displayString ($source) {
        try {
            // compile
            $parser = new SugarParser($this);
            $data = $parser->compile($source);
            $parser = null;

            // run
            $this->execute($data);
        } catch (SugarException $e) {
            echo '<p><b>'.htmlentities($e->__toString()).'</b></p>';
            return false;
        }
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
