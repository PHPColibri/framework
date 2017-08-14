<?php
namespace Colibri\Session;

use Colibri\Pattern\Helper;
use Colibri\Session\Storage\Native;
use Colibri\Session\Storage\StorageInterface;
use Colibri\Util\Arr;

/**
 * Class Session.
 */
class Session extends Helper
{
    /**
     * @var array flashed between http calls variables
     */
    private static $flashedVars = [];
    /**
     * @var StorageInterface storage driver
     */
    private static $storage = null;

    /**
     * Starts the Session.
     */
    public static function start()
    {
        self::$storage     = Native::getInstance();
        self::$flashedVars = self::$storage->remove('flashed');
    }

    /**
     * Retrieve the value from session.
     *
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
     * Store the value into session.
     *
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
     * Removes the value from session by specified key.
     *
     * @param $dottedKey
     *
     * @return mixed|null returns removed value or null if key not found
     */
    public static function remove($dottedKey)
    {
        return self::$storage->remove($dottedKey);
    }

    /**
     * Flashes some value to next http call.
     *
     * @param string $key
     * @param mixed  $value
     */
    public static function flash($key, $value)
    {
        static::set('flashed.' . $key, $value);
    }

    /**
     * Flashes array of values to next http call.
     *
     * @param array $keyValues
     */
    public static function flashValues(array $keyValues)
    {
        static::set('flashed', $keyValues);
    }

    /**
     * Closes current session and try to find and open new with <$sessionId>.
     *
     * @param string $id
     * @param bool   $saveCurrent
     *
     * @throws \Colibri\Session\Exception
     */
    public static function catch($id, $saveCurrent = true)
    {
        self::$storage->catch($id, $saveCurrent);
    }
}
