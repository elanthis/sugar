<?php
interface ISugarStorage {
    function stamp (SugarRef $ref);
    function load (SugarRef $ref);
    function path (SugarRef $ref);
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
