<?php
namespace Colibri\Config;

use Colibri\Pattern\Helper;
use Colibri\Util\Arr;

/**
 * Config
 *
 * @method static mixed application(string $key, mixed $default = null) gets config value by keys, separated with dot
 */
class Config extends Helper
{
    /**
     * @var array in-memory cache
     */
    protected static $allLoadedConfigs = [];
    /**
     * @var string path to directory with config files
     */
    protected static $baseDir = null;

    /**
     * Retrieve full real path to config.
     *
     * @param string $name config file name
     *
     * @return string
     * @throws \InvalidArgumentException if can`t get the real-path of config file
     */
    protected static function getFilepath($name)
    {
        return static::getBaseDir() . '/' . $name . '.php';
    }

    /**
     * @param string $name config file name
     *
     * @return array
     * @throws \InvalidArgumentException if can`t get the real-path of config file
     */
    protected static function load($name)
    {
        /** @noinspection PhpIncludeInspection */
        return include(static::getFilepath($name));
    }

    /**
     * @param string $name config file name
     *
     * @return bool
     * @throws \InvalidArgumentException if can`t get the real-path of config file
     */
    public static function exists($name)
    {
        return file_exists(static::getFilepath($name));
    }

    /**
     * @param string $name config file name
     *
     * @return array
     * @throws \InvalidArgumentException if can`t get the real-path of config file
     */
    final public static function get($name)
    {
        return isset(self::$allLoadedConfigs[$name])
            ? self::$allLoadedConfigs[$name]
            : self::$allLoadedConfigs[$name] = Arr::overwrite(static::load($name), LocalConfig::load($name));
    }

    /**
     * Returns config if exists or empty array if not
     *
     * @param string $name config file name
     *
     * @return array
     * @throws \InvalidArgumentException if can`t get the real-path of config file
     */
    final public static function getOrEmpty($name)
    {
        return static::exists($name) ? self::get($name) : [];
    }

    /**
     * Sets path of the directory where config files are stored.
     *
     * @param string $path
     *
     * @return string returns the real path
     * @throws \InvalidArgumentException if can`t get the real-path of config file
     */
    public static function setBaseDir($path)
    {
        $path = realpath(rtrim($path, '/\\ '));
        if ($path === false)
            throw new \InvalidArgumentException("cat`t get real path: seems like path does`t exists: $path");

        return static::$baseDir = $path;
    }

    /**
     * @return string
     * @throws \InvalidArgumentException if can`t get the real-path of config file
     */
    public static function getBaseDir()
    {
        return static::$baseDir === null
            ? static::setBaseDir(__DIR__ . '/../../../../application/configs')
            : static::$baseDir;
    }

    /**
     * @param string $name      config file name
     * @param array  $arguments (string $key, mixed $default = null)
     *
     * @return mixed
     * @throws \InvalidArgumentException if can`t get the real-path of config file
     */
    public static function __callStatic($name, $arguments)
    {
        $key     = isset($arguments[0]) ? $arguments[0] : null;
        $default = isset($arguments[1]) ? $arguments[1] : null;

        return Arr::get(static::get($name), $key, $default);
    }
}
