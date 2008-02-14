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
 * @author Sean Middleditch <sean@awesomeplay.com>
 * @copyright 2008 AwesomePlay Productions, Inc. and contributors
 * @license http://opensource.org/licenses/mit-license.php MIT
 */

// read in StdLib.php
$file = file_get_contents('Sugar/Stdlib.php');
if (!$file)
	die('File not found');

// parse out lines
$doclines = preg_grep('/\*\+/', explode("\n", $file));

// read in doc blocks
$blocks = array();
$block = array();
foreach($doclines as $line) {
	// new block?
	if (preg_match('/\*\+\+/', $line)) {
		if ($block) {
			$blocks [$block['name']]= $block;
			$block = array();
		}
		continue;
	}

	// get line data
	$line = preg_replace('/.*\*\+\s?(.*?)\s*$/', '\1', $line);
	
	// attribute?
	if (preg_match('/@(\w+)\s*(.*)$/', $line, $ar)) {
		switch ($ar[1]) {
		case 'name':
			$block['name'] = $ar[2];
			break;
		case 'alias':
			$block['alias'] []= $ar[2];
			break;
		case 'param':
			if (!preg_match('/([\w|]+)(\??)\s+\$(\w+)\s+(.*)/', $ar[2], $ar))
				die('Malformed param attribute: '.$line);
			$block['param'] []= array('type' => $ar[1], 'optional' => ($ar[2] == '?' ? true : false), 'name' => $ar[3], 'doc' => $ar[4]);
			break;
		case 'return':
			if (!preg_match('/([\w|]+)\s+(.*)/', $ar[2], $ar))
				die('Malformed return attribute: '.$line);
			$block['return'] = array('type' => $ar[1], 'doc' => $ar[2]);
			break;
		case 'varargs':
			$block['varargs'] = $ar[2];
			break;
		default:
			die('Unknown attribute '.$ar[1]);
		}
	} else {
		if ($block['doc'] || $line)
			$block['doc'] []= $line;
	}
}
if ($block)
	$blocks [$block['name']]= $block;

// sort
ksort($blocks);

// display
require_once './Sugar.php';
$sugar = new Sugar();
$sugar->cacheDir = './test/templates/cache';
$sugar->templateDir = '.';
$sugar->set('blocks', $blocks);
$sugar->display('gen-doc.tpl');
