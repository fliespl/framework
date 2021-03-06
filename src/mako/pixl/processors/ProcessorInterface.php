<?php

namespace mako\pixl\processors;

use \mako\pixl\Image;

/**
 * Image manipulation processor interface.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2014 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

interface ProcessorInterface
{
	public function __construct();

	public function setImage($image);

	public function rotate($degrees);

	public function resize($width, $height = null, $aspectRatio = Image::RESIZE_IGNORE);

	public function crop($width, $height, $x, $y);

	public function flip($direction = Image::FLIP_HORIZONTAL);

	public function watermark($file, $position = Image::WATERMARK_TOP_LEFT, $opacity = 100);

	public function greyscale();

	public function colorize($color);

	public function border($color = '#000', $thickness = 5);

	public function getImageBlob($type = null, $quality = 95);

	public function save($file, $quality = 95);
}

/** -------------------- End of file -------------------- **/