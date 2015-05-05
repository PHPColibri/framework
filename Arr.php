<?php
namespace Colibri\Util;
use Colibri\Pattern\Helper;

/**
 * Description of Arr
 *
 * @author Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 */
class Arr extends Helper
{
	/**
	 * 
	 * @param array $array
	 * @param array $with
	 * @return array
	 */
	public static function overwrite(array $array, array $with)
	{
		return array_replace_recursive($array, $with);
	}
	/**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param  array   $array
	 * @param  string  $dottedKey
	 * @param  mixed   $default
	 * @return mixed
	 */
	public static function get(array $array, $dottedKey = null, $default = null)
	{
		if (is_null($dottedKey)) return $array;
		if (isset($array[$dottedKey])) return $array[$dottedKey];

		foreach (explode('.', $dottedKey) as $segment) {
			if (!is_array($array) || !array_key_exists($segment, $array)) {
				return $default;
			}
			$array = $array[$segment];
		}

		return $array;
	}

	/**
	 * @param array  $array
	 * @param string $dottedKey
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public static function &set(&$array, $dottedKey, $value)
	{
		if (is_null($dottedKey)) return $array = $value;

		$k = explode('.', $dottedKey, 2);

		$array[$k[0]] = isset($k[1])
			? Arr::set($array[$k[0]], $k[1], $value)
			: $value;

		return $array;
	}

	/**
	 * @param array  $array
	 * @param string $dottedKey
	 *
	 * @return mixed|null
	 */
	public static function remove(array &$array, $dottedKey)
	{
		if (isset($array[$dottedKey])) {
			$value = $array[$dottedKey];
			unset($array[$dottedKey]);
			return $value;
		}

		$k = explode('.', $dottedKey, 2);
		return isset($k[1])
			? Arr::remove($array[$k[0]], $k[1])
			: null;
	}
}
