<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// width/height attributes for an uploaded picture, read from the file itself, so
// the browser keeps the picture's space before it has loaded and the text below
// does not jump down when it arrives (client, 24 Nov 2026). Returns '' when the
// file is missing or is not a picture, so the <img> is printed exactly as before.
if (!function_exists('image_size_attrs')) {
	function image_size_attrs($file)
	{
		if (!is_string($file) || $file === '' || !is_file($file)) {
			return '';
		}
		$size = @getimagesize($file);
		if (!$size || (int) $size[0] < 1 || (int) $size[1] < 1) {
			return '';
		}
		return ' width="' . (int) $size[0] . '" height="' . (int) $size[1] . '"';
	}
}
