<?php
namespace Colibri\Http;

use Colibri\Pattern\Helper;

class Redirect extends Helper
{
	/**
	 * @param $url
	 */
	public static function to($url)
	{
		return header('Location: ' . $url);
	}
}