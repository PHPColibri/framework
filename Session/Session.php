<?php
namespace Colibri\Session;

use Colibri\Pattern\Helper;
use Colibri\Session\Storage\Native;
use Colibri\Session\Storage\StorageInterface;
use Colibri\Util\Arr;

/**
 * Class Session
 */
class Session extends Helper
{
	private static $flashedVars = array();
	/**
	 * @var StorageInterface
	 */
	private static $storage = null;


	public static function start()
	{
		self::$storage     = Native::getInstance();
		self::$flashedVars = self::$storage->remove('flashed');
	}

	/**
	 * @param string $dottedKey
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public static function get($dottedKey, $default = null)
	{
		if (self::$flashedVars) {
			$flashedValue = Arr::get(self::$flashedVars, $dottedKey, '~no~flashed~value~');
			if ($flashedValue !== '~no~flashed~value~') {
				return $flashedValue;
			}
		}

		return self::$storage->get($dottedKey, $default);
	}

	/**
	 * @param string $dottedKey
	 * @param mixed  $value
	 *
	 * @return mixed
	 */
	public static function set($dottedKey, $value)
	{
		return self::$storage->set($dottedKey, $value);
    }

	/**
	 * @param $dottedKey
	 *
	 * @return mixed|null returns removed value or null if key not found
	 */
	public static function remove($dottedKey)
	{
		return self::$storage->remove($dottedKey);
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 */
	public static function flash($key, $value)
	{
		static::set('flashed.' . $key, $value);
	}

	/**
	 * @param array $keyValues
	 */
	public static function flashValues(array $keyValues)
	{
		static::set('flashed', $keyValues);
	}
}