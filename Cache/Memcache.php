<?php

namespace Colibri\Cache;

use Colibri\Config\Config;
use Colibri\Pattern\Helper;
use Colibri\Util\Arr;

/**
 *
 *
 * @author         Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package        xTeam
 * @subpackage     a13FW
 * @version        2.0.3
 */
class Memcache extends Helper implements ICache
{
    static private $defaultConfig = [
        'server'            => '127.0.0.1',
        'port'              => 11211,
        'defaultExpiration' => 300,
    ];

    static private $defaultExpiration = null;
    /**
     * @var \Memcache
     */
    static private $memcache = null;
    /**
     * @var int
     */
    static private $queriesCount = 0;


    /**
     * @return int
     */
    static public function getQueriesCount()
    {
        return self::$queriesCount;
    }

    /**
     * @return \Memcache
     */
    static private function getMemcache()
    {
        if (self::$memcache !== null)
            return self::$memcache;

        $config = self::getConfig();

        self::$defaultExpiration = $config['defaultExpiration'];

        self::$memcache = new \Memcache();
        self::$memcache->connect(
            $config['server'],
            $config['port']
        );

        return self::$memcache;
    }

    static private function getConfig()
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
     * @return bool|mixed result OR data
     */
    static
    public function get($key)
    {
        self::$queriesCount++;

        return ($tmp = self::getMemcache()->get($key)) === false
            ? false
            : unserialize($tmp);
    }

    /**
     * @param string $key for data
     * @param mixed  $val any type of supported data: object, string, int…
     * @param int    $expire
     *
     * @return bool result
     */
    static
    public function set($key, $val, $expire = null)
    {
        self::$queriesCount++;

        return self::getMemcache()
            ->set(
                $key,
                serialize($val),
                !MEMCACHE_COMPRESSED, // @todo здесь какая-то лажа (надо бы перейти на Memcached)
                $expire !== null ? $expire : self::$defaultExpiration
            )
            ;
    }

    /**
     *
     * @param string $key
     *
     * @return boolean true on success and false on fail.
     */
    static
    public function delete($key)
    {
        self::$queriesCount++;

        return self::getMemcache()->delete($key);
    }

    /**
     *
     * @param string   $key
     * @param \Closure $getValueCallback
     *
     * @param int      $expire
     *
     * @return mixed
     */
    public static function remember($key, \Closure $getValueCallback, $expire = null)
    {
        if (($fromCache = static::get($key)) !== false)
            return $fromCache;

        static::set($key, $value = $getValueCallback(), $expire);

        return $value;
    }
}
