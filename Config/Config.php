<?php
namespace Colibri\Config;

use Colibri\Pattern\Helper;
use Colibri\Util\Arr;

/**
 * Description of Config
 *
 * @author Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @method static mixed application(string $key, mixed $default=null) gets config value by keys, separated with dot
 */
class Config extends Helper
{
	protected static $allLoadedConfigs	 = array();
	protected static $baseDir			 = null;

	protected static function getFilepath($name)
	{
		return static::getBaseDir() . '/' . $name . '.php';
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	protected static function load($name)
	{
		/** @noinspection PhpIncludeInspection */
		return include(static::getFilepath($name));
	}

	public static function exists($name)
	{
		return file_exists(static::getFilepath($name));
	}

	/**
	 * @param string $name
	 * @return array
	 */
	final public static function get($name)
	{
		return isset(self::$allLoadedConfigs[$name])
			? self::$allLoadedConfigs[$name]
			: self::$allLoadedConfigs[$name] = Arr::overwrite(static::load($name), LocalConfig::load($name))
		;
	}

	/**
	 * Returns config if exists or empty array if not
	 * 
	 * @param string $name
	 * @return array
	 */
	final public static function getOrEmpty($name)
	{
		return static::exists($name) ? self::get($name) : array();
	}

	/**
	 * 
	 * @param string $path
	 * @return string
	 * @throws \Exception
	 */
	public static function setBaseDir($path)
	{
		$realpath = realpath(rtrim($path, '/\\ '));
		if ($realpath === false)
			throw new \Exception("cat`t get realpath: seems like path does`t exists: $path");
		
		return static::$baseDir = realpath(rtrim($path, '/\\ '));
	}

	public static function getBaseDir()
	{
		return static::$baseDir === null
			? static::setBaseDir(__DIR__ . '/../../../../application/configs')
			: static::$baseDir
		;
	}

	public static function __callStatic($name, $arguments)
	{
		$key	 = isset($arguments[0]) ? $arguments[0] : null;
		$default = isset($arguments[1]) ? $arguments[1] : null;
		
		return Arr::get(static::get($name), $key, $default);
	}
}
