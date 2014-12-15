<?php
namespace Colibri\Util;

/**
 * Description of image
 *
 * @author		Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package		xTeam
 * @version		1.00.0
 */
class Image
{
	const RESIZE_FILLED  = 'filled' ;
	const RESIZE_CROPPED = 'cropped';

	/**
	 *
	 * @param string $path
	 * @param int $target_width
	 * @param int $target_height
	 * @param int $bgColor
	 * @return string binary stream with thumbnail
	 * @throws \Exception
	 */
static
	public		function	createThumbnail($path, $target_width=100, $target_height=100, $bgColor=0xdddddd)
	{
		$img = static::load($path);

		list($width, $height) = static::getSize($img);
		
		// Calc thumbnail size
		$target_ratio = $target_width / $target_height;
		   $img_ratio =	       $width /        $height;

		if ($target_ratio > $img_ratio)	{
			$new_width  = $img_ratio * $target_height;
			$new_height = $target_height;
		} else {
			$new_width  = $target_width;
			$new_height = $target_width / $img_ratio;
		}

		if ($new_height > $target_height)
			$new_height = $target_height;
		if ($new_width > $target_width)
			$new_height = $target_width;

		// Build the thumbnail
		$thumbnail = ImageCreateTrueColor($target_width, $target_height);
		if (!$thumbnail)
			throw new \Exception('can`t create thumbnail: ImageCreateTrueColor failed');
		// Fill the image with bgColor
		if (!@imagefilledrectangle($thumbnail, 0, 0, $target_width-1, $target_height-1, $bgColor))
			throw new \Exception('can`t create thumbnail: can`t fill new image by bg-color');
		// Resize image into thumbnail
		if (!@imagecopyresampled($thumbnail, $img, ($target_width-$new_width)/2, ($target_height-$new_height)/2, 0, 0, $new_width, $new_height, $width, $height))
			throw new \Exception('can`t create thumbnail: can`t resize image');

		return static::getIntoVariable($thumbnail);
	}
	
	/**
	 * 
	 * @param string $path
	 * @return resource GD image resource
	 * @throws \Exception
	 */
static
	protected	function	load($path)
	{
		$mime = File::getMimeType($path);
		switch ($mime) {
			case 'image/jpeg':	$img = imagecreatefromjpeg($path); break;
			case 'image/gif':	$img = imagecreatefromgif ($path); break;
			case 'image/png':	$img = imagecreatefrompng ($path); break;
			case 'image/bmp':	$img = imagecreatefromwbmp($path); break;
			default:			throw new \Exception('can`t create thumbnail: unknown image type');
		}
		if (!$img)
			throw new \Exception('can`t create thumbnail: can`t create image handle from file '.$path);
		
		return $img;
	}
	/**
	 * 
	 * @param resource $img GD image resource
	 * @return array [width, height]
	 * @throws \Exception
	 */
static
	protected	function	getSize($img)
	{
		$width  = imageSX($img);
		$height = imageSY($img);
		if (!$width || !$height)
			throw new \Exception('can`t create thumbnail: can`t get image size, invalid image width or height');
		
		return array($width, $height);
	}
	/**
	 * 
	 * @param resource $img GD image resource
	 * @return string
	 * @throws \Exception
	 */
static
	protected	function	getIntoVariable($img)
	{
		// Use a output buffering to load the image into a variable
		ob_start();
		if (!@imagejpeg($img))
		{
			ob_end_clean();
			throw new \Exception('can`t create thumbnail: can`t output thumbnail into var');
		}
		$imageVariable = ob_get_contents();
		ob_end_clean();
		
		return $imageVariable;
	}
}
