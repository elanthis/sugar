<?php
interface ISugarStorage {
    function stamp ($name);
    function load ($name);
    function path ($name);
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
