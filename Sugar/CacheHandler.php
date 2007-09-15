<?php
/**
 * PHP-Sugar Template Engine
 *
 * Copyright (c) 2007  AwesomePlay Productions, Inc. and
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
 * @subpackage Internals
 * @author Sean Middleditch <sean@awesomeplay.com>
 * @copyright 2007 AwesomePlay Productions, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

/**
 * Handlers the creation of Sugar caches, including non-cached bytecode
 * chunks.
 *
 * @package Sugar
 * @subpackage Internals
 */
class SugarCacheHandler {
    /**
     * Sugar reference.
     *
     * @var Sugar $sugar
     */
    private $sugar;

    /**
     * Text output.
     *
     * @var string $output
     */
    private $output;

    /**
     * Bytecode result.
     *
     * @var array $bc
     */
    private $bc;

    /**
     * Compresses the text output gathered so far onto the bytecode stack.
     */
    private function compact () {
        if ($this->output) {
            $this->bc []= 'echo';
            $this->bc []= $this->output;
            $this->output = '';
        }
    }

    /**
     * Constructor.
     *
     * @param Sugar $sugar Sugar reference.
     */
    public function __construct ($sugar) {
        $this->sugar = $sugar;
    }

    /**
     * Adds text to the cache.
     *
     * @param string $text Text to append to cache.
     */
    public function addOutput ($text) {
        $this->output .= $text;
    }

    /**
     * Adds bytecode to the cache.
     *
     * @param array $block Bytecode to append to cache.
     */
    public function addBlock ($block) {
        $this->compact();
        array_push($this->bc, 'nocache', $block);
    }

    /**
     * Returns the complete cache.
     *
     * @return array Cache.
     */
    public function getOutput () {
        $this->compact();
        return array('type' => 'chtml', 'version' => SUGAR_VERSION, 'bytecode' => $this->bc);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 : ?>