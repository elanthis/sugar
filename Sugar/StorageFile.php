<?php
/**
 * File-based storage driver.
 *
 * This provides the default filesystem storage driver for Sugar cache files.
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
 * @subpackage Drivers
 * @author Sean Middleditch <sean@mojodo.com>
 * @copyright 2008,2009 Mojodo, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 * @version 0.82
 * @link http://php-sugar.net
 */

/**
 * File-based storage driver.
 *
 * Uses {@link Sugar::$templateDir} to find templates.
 *
 * This class is a namespace containing static function relevant to 
 * executing Sugar bytecode.
 *
 * @category Template
 * @package Sugar
 * @subpackage Drivers
 * @author Sean Middleditch <sean@mojodo.com>
 * @copyright 2008,2009 Mojodo, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 * @version 0.82
 * @link http://php-sugar.net
 */
class SugarStorageFile implements ISugarStorage
{
    /**
     * Sugar instances.
     *
     * @var Sugar $sugar
     */
    private $sugar;

    /**
     * Constructor.
     *
     * @param Sugar $sugar Sugar instance.
     */
    public function __construct($sugar)
    {
        $this->sugar = $sugar;
    }

    /**
     * Returns the timestamp of the reference, or 0 if the reference does
     * not exist.
     *
     * @param SugarRef $ref Reference to lookup.
     * @return int Timestamp if it exists, or zero if it cannot be found.
     */
    public function stamp(SugarRef $ref)
    {
        $path = $this->sugar->templateDir.'/'.$ref->name.'.tpl';
        if (is_file($path) && is_readable($path))
            return filemtime($path);
        else
            return false;
    }

    /**
     * Returns the source for the requested reference.
     *
     * @param SugarRef $ref Reference to lookup.
     * @return string Source of reference.
     */
    public function load(SugarRef $ref)
    {
        $path = $this->sugar->templateDir.'/'.$ref->name.'.tpl';
        if (is_file($path) && is_readable($path))
            return file_get_contents($path);
        else
            return false;
    }

    /**
     * Returns a path name for the reference, mapped as appropriate for
     * the driver.  This is used for error messages.  The result should
     * make it easier for the template writer to identify which template
     * is being refrenced.  Returning {@link SugarRef::$full} may be
     * adequate for many drivers.
     *
     * @param SugarRef $ref Reference to lookup.
     * @return string User-friendly path to reference.
     */
    public function path(SugarRef $ref)
    {
        return $this->sugar->templateDir.'/'.$ref->name.'.tpl';
    }
}
// vim: set expandtab shiftwidth=4 tabstop=4 :
?>
