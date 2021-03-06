#!/usr/bin/php
<?php
/**
 * Simple input filter for using Doxygen with PHP code
 *
 * It escapes backslashes in docblock comments, and appends variable names to
 * \@var commands.
 *
 * @see http://www.doxygen.org/
 * @see http://www.stack.nl/~dimitri/doxygen/config.html#cfg_input_filter
 *
 * Copyright (C) 2012 Tamas Imrei <tamas.imrei@gmail.com>
 *                         http://virtualtee.blogspot.com/
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

$source = file_get_contents($_SERVER['argv'][1]);
$tokens = token_get_all($source);

$buffer = null;
foreach ($tokens as $token) {
	if (is_string($token)) {
		if ((! empty($buffer)) && ($token == ';')) {
			echo $buffer;
			unset($buffer);
		}
		echo $token;
	} else {
		list($id, $text) = $token;
		switch ($id) {
			case T_DOC_COMMENT :
				$text = addcslashes($text, '\\');
				if (preg_match('#@var\s+[^\$]*\*/#ms', $text)) {
					$buffer = preg_replace('#(@var\s+[^\n\r]+)(\n\r?.*\*/)#ms',
						'$1 \$\$\$$2', $text);
				} else {
					echo $text;
				}
				break;

			case T_VARIABLE :
				if ((! empty($buffer))) {
					echo str_replace('$$$', $text, $buffer);
					unset($buffer);
				}
				echo $text;
				break;

			default:
				if ((! empty($buffer))) {
					$buffer .= $text;
				} else {
					echo $text;
				}
				break;
		}
	}
}
?>