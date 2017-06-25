<?php
namespace Colibri\Cache;

use Colibri\Config\Config;
use Colibri\Pattern\Helper;
use Colibri\Util\Arr;

/**
 * Memcache implementation of Cache
 */
class Memcache extends Helper implements ICache
{
    private static $defaultConfig = [
        'server'            => '127.0.0.1',
        'port'              => 11211,
        'defaultExpiration' => 300,
    ];

    private static $defaultExpiration = null;
    /**
     * @var \Memcache
     */
    private static $memcache = null;
    /**
     * @var int count of queries to Memcache statistics
     */
    private static $queriesCount = 0;


    /**
     * @return int count of queries to Memcache statistics
     */
    public static function getQueriesCount()
    {
        return self::$queriesCount;
    }

    /**
     * @return \Memcache
     * @throws \InvalidArgumentException
     */
    private static function getMemcache()
    {
        if (self::$memcache !== null) {
            return self::$memcache;
        }

        $config = self::getConfig();

        self::$defaultExpiration = $config['defaultExpiration'];

        self::$memcache = new \Memcache();
        self::$memcache->connect(
            $config['server'],
            $config['port']
        );

        return self::$memcache;
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    private static function getConfig()
    {
        $config = Config::getOrEmpty('cache');

        return Arr::overwrite(
            static::$defaultConfig,
            isset($config['memcache'])
                ? $config['memcache']
                : []
        );
    }

    /**
     * @param string $key for data
     *
     * @return bool|mixed returns false if failed OR cached data
     * @throws \InvalidArgumentException
     */
    public static function get($key)
    {
        self::$queriesCount++;

        return ($tmp = self::getMemcache()->get($key)) === false
            ? false
            : unserialize($tmp);
    }

    /**
     * @param string $key    for data
     * @param mixed  $val    any type of supported data: object, string, int…
     * @param int    $expire Expiration time of the item (usually in seconds).
     *                       If it's equal to null, the 'defaultExpiration' config value will used.
     *                       If it's equal to zero, the item will never expire.
     *                       You can also use Unix timestamp or a number of seconds starting from current time,
     *                       but in the latter case the number of seconds may not exceed 2592000 (30 days).
     *
     * @return bool Returns <b>TRUE</b> on success or <b>FALSE</b> on failure.
     * @throws \InvalidArgumentException
     */
    public static function set($key, $val, $expire = null)
    {
        self::$queriesCount++;

        return self::getMemcache()
            ->set(
                $key,
                serialize($val),
                ! MEMCACHE_COMPRESSED, // @todo здесь какая-то лажа (надо бы перейти на Memcached)
                $expire !== null ? $expire : self::$defaultExpiration
            )
            ;
    }

    /**
     *
     * @param string $key
     *
     * @return bool true on success and false on fail.
     * @throws \InvalidArgumentException
     */
    public static function delete($key)
    {
        self::$queriesCount++;

        return self::getMemcache()->delete($key);
    }

    /**
     * Tries to ::get() data from cache; and if not exists,
     *   get the real date through $getValueCallback()
     *   and store it in cache, than return
     *
     * @param string   $key              key for data
     * @param \Closure $getValueCallback closure that get the real (not cached) value
     * @param int      $expire           Expiration time of the item (usually in seconds).
     *                                   If it's equal to null, the 'defaultExpiration' config value will used.
     *                                   If it's equal to zero, the item will never expire.
     *                                   You can also use Unix timestamp or a number of seconds starting from current
     *                                   time, but in the latter case the number of seconds may not exceed 2592000 (30
     *                                   days).
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public static function remember($key, \Closure $getValueCallback, $expire = null)
    {
        if (($fromCache = static::get($key)) !== false) {
            return $fromCache;
        }

        static::set($key, $value = $getValueCallback(), $expire);

        return $value;
    }
}
