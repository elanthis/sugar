<?php
/**
 * PHP-Sugar Template Engine
 *
 * Copyright (c) 2008  AwesomePlay Productions, Inc. and
 * contributors.  All rights reserved.
 *
 * LICENSE:
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package Sugar
 * @subpackage Exceptions
 * @author Sean Middleditch <sean@awesomeplay.com>
 * @copyright 2008 AwesomePlay Productions, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

/**
 * Generic Sugar exception.
 *
 * @package Sugar
 * @subpackage Exceptions
 */
class SugarException extends Exception {
    /**
     * Constructor.
     *
     * @param string $msg Error message.
     */
    public function __construct ($msg) {
        parent::__construct($msg);
    }
}

/**
 * Parse error.
 *
 * @package Sugar
 * @subpackage Exceptions
 */
class SugarParseException extends SugarException {
    /**
     * File error occured in.
     *
     * @var string $file
     */
    var $file = '<input>';

    /**
     * Line error occured in.
     *
     * @var int $line
     */
    var $line = 1;

    /**
     * Error message.
     *
     * @var string Error message.
     */
    var $msg;

    /**
     * Constructor.
     *
     * @param string $file File the error occured in.
     * @param int $line Line the error occured in.
     * @param string $msg Error message.
     */
    public function __construct ($file, $line, $msg) {
        parent::__construct('parse error at '.$file.','.$line.': '.$msg);
        $this->file = $file;
        $this->line = $line;
    }
}

/**
 * Runtime error.
 *
 * @package Sugar
 * @subpackage Exceptions
 */
class SugarRuntimeException extends SugarException {
    /**
     * File error occured in.
     *
     * @var string $file
     */
    var $file = '<input>';

    /**
     * Line error occured in.
     *
     * @var int $line
     */
    var $line = 1;

    /**
     * Error message.
     *
     * @var string Error message.
     */
    var $msg;

    /**
     * Constructor.
     *
     * @param string $file File the error occured in.
     * @param int $line Line the error occured in.
     * @param string $msg Error message.
     */
    public function __construct ($file, $line, $msg) {
        parent::__construct('runtime error at '.$file.','.$line.': '.$msg);
        $this->file = $file;
        $this->line = $line;
    }
}

// vim: set expandtab shiftwidth=4 tabstop=4 : ?>
