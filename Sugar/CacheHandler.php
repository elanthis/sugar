<?php
/**
 * Cache handler, a helper class for the runtime.
 *
 * This class is used to build up the cache output for cached runs of the
 * runtime engine.  It provides methods for storing cached output as well
 * as adding uncachable runtime code to be re-executed when the cache is
 * displayed.
 *
 * PHP version 5
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
 * @category Template
 * @package Sugar
 * @subpackage Runtime
 * @author Sean Middleditch <sean@mojodo.com>
 * @copyright 2008,2009 Mojodo, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 * @version 0.81
 * @link http://php-sugar.net
 * @access private
 */

/**
 * Handlers the creation of Sugar caches, including non-cached bytecode
 * chunks.
 *
 * @category Template
 * @package Sugar
 * @subpackage Runtime
 * @author Sean Middleditch <sean@mojodo.com>
 * @copyright 2008,2009 Mojodo, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 * @version 0.81
 * @link http://php-sugar.net
 * @access private
 */
class SugarCacheHandler
{
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
     * List of file references used, stored as strings.
     *
     * @var array $refs
     */
    private $refs;

    /**
     * Compresses the text output gathered so far onto the bytecode stack.
     */
    private function compact()
    {
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
    public function __construct($sugar)
    {
        $this->sugar = $sugar;
    }

    /**
     * Adds text to the cache.
     *
     * @param string $text Text to append to cache.
     */
    public function addOutput($text)
    {
        $this->output .= $text;
    }

    /**
     * Adds a new file reference to the list of files
     * used in the template.
     *
     * @param SugarRef $ref New reference.
     */
    public function addRef(SugarRef $ref)
    {
        $this->refs []= $ref->full;
    }

    /**
     * Adds bytecode to the cache.
     *
     * @param array $block Bytecode to append to cache.
     */
    public function addBlock($block)
    {
        $this->compact();
        array_push($this->bc, 'nocache', $block);
    }

    /**
     * Returns the complete cache.
     *
     * @return array Cache.
     */
    public function getOutput()
    {
        $this->compact();
        return array('type' => 'chtml', 'version' => Sugar::VERSION, 'refs' => $this->refs, 'bytecode' => $this->bc);
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
