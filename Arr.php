<?php
namespace Colibri\Util;

/**
 * Description of Arr
 *
 * @author Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 */
class Arr
{
	/**
	 * 
	 * @param array $array
	 * @param array $with
	 * @return type
	 */
	public static function overwrite(array $array, array $with)
	{
		return array_replace_recursive($array, $with);
	}
	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	public static function get(array $array, $key = null, $default = null)
	{
		if (is_null($key)) return $array;
		if (isset($array[$key])) return $array[$key];

		foreach (explode('.', $key) as $segment) {
			if (!is_array($array) || !array_key_exists($segment, $array)) {
				return $default;
			}
			$array = $array[$segment];
		}

		return $array;
	}

}
