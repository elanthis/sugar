<?php
class SugarStorageFile implements ISugarStorage {
    private $sugar;

    public function __construct (&$sugar) {
        $this->sugar =& $sugar;
    }

    public function stamp (SugarRef $ref) {
        $path = $this->sugar->templateDir.'/'.$ref->name.'.tpl';
        if (is_file($path) && is_readable($path))
            return filemtime($path);
        else
            return false;
    }

    public function load (SugarRef $ref) {
        $path = $this->sugar->templateDir.'/'.$ref->name.'.tpl';
        if (is_file($path) && is_readable($path))
            return file_get_contents($path);
        else
            return false;
    }

    public function path (SugarRef $ref) {
        return $this->sugar->templateDir.'/'.$ref->name.'.tpl';
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
