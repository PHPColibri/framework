<?php
namespace Colibri\Cache;

use Colibri\Cache\Storage\Memcache;
use Colibri\Pattern\Helper;
use Colibri\Util\Arr;

/**
 * Cache drivers wrapper.
 */
class Cache extends Helper implements CacheInterface
{
    /**
     * @var array
     */
    private static $config = [
        'default-storage' => 'memcache',
        'default-ttl'     => 300,
        'storage'         => [
            'memcache' => [
                'driver' => 'memcache',
                'config' => [],
            ],
        ],
    ];
    /**
     * @var array|Storage\StorageInterface[]
     */
    private static $storage = [];
    /**
     * @var array
     */
    private static $driver = [
        'memcache' => Memcache::class,
    ];

    /**
     * @param array $config
     */
    public static function setConfig(array $config)
    {
        self::$config['storage'] = Arr::overwrite(self::$config['storage'], $config);
    }

    /**
     * @return int count of queries to Memcache statistics
     */
    public static function getQueriesCount(): int
    {
        return array_sum(array_map(function (Storage\StorageInterface $storage) {
            return $storage->getQueriesCount();
        }, self::$storage));
    }

    /**
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function get(string $key, $default = null)
    {
        return self::storage()->get($key, $default);
    }

    /**
     * @param string $key    key for data
     * @param mixed  $value  any type of supported data: object, string, int…
     * @param int    $expire seconds
     *
     * @return bool true on success and false on failure
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function set(string $key, $value, int $expire = null): bool
    {
        return self::storage()->set($key, $value, $expire);
    }

    /**
     * @param string $key the unique cache key of the item to delete
     *
     * @return bool true on success and false on failure
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function delete(string $key): bool
    {
        return self::storage()->delete($key);
    }

    /**
     * Tries to ::get() data from cache; and if not exists,
     *   get the real date through $getValueCallback()
     *   and store it in cache, than return.
     *
     * @param string   $key              key for data
     * @param \Closure $getValueCallback closure that get the real (not cached) value
     * @param int|null $expire           seconds
     *
     * @return mixed returns cached data
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function remember(string $key, \Closure $getValueCallback, int $expire = null)
    {
        return self::storage()->remember($key, $getValueCallback, $expire);
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return \Colibri\Cache\Storage\StorageInterface
     */
    public static function __callStatic(string $name, array $arguments): Storage\StorageInterface
    {
        return static::storage($name);
    }

    /**
     * @param string $name
     *
     * @return \Colibri\Cache\Storage\StorageInterface
     */
    private static function storage(string $name = 'default'): Storage\StorageInterface
    {
        $name = $name == 'default' ? (self::$config['storage']['default'] ?? self::$config['default-storage']) : $name;

        return isset(self::$storage[$name])
            ? self::$storage[$name]
            : self::$storage[$name] = self::createFromConfig($name);
    }

    /**
     * @param $name
     *
     * @return Storage\StorageInterface
     */
    private static function createFromConfig($name): Storage\StorageInterface
    {
        $config = &self::$config['storage'][$name];
        $driver = &$config['driver'];
        $config = &$config['config'];

        return new self::$driver[$driver]($config);
    }

    /**
     * Аннулирует все записи в кеше.
     * Clears all cached values.
     */
    public static function flush()
    {
        static::storage()->flash();
    }
}
