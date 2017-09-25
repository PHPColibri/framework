<?php
namespace Colibri\Cache\Storage;

use Colibri\Cache\Storage\Exception\InvalidArgumentException;
use Colibri\Util\Arr;

/**
 * Memcache implementation of Cache.
 */
class Memcache extends AbstractStorage implements StorageInterface
{
    /** @var array */
    private static $defaultConfig = [
        'server' => '127.0.0.1',
        'port'   => 11211,
    ];

    /** @var int|null */
    private static $defaultExpiration = 300;

    /**
     * @var \Memcache
     */
    private $memcache = null;

    /**
     * Memcache constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $config = Arr::overwrite(self::$defaultConfig, $config);

        self::$defaultExpiration = $config['defaultExpiration'] ?? self::$defaultExpiration;

        $this->memcache = new \Memcache();
        $this->memcache->connect($config['server'], $config['port']);
    }

    /**
     * @param string|array $key for data
     * @param mixed|null   $default
     *
     * @return bool|mixed returns false if failed OR cached data
     *
     * @throws \Colibri\Cache\Storage\Exception\InvalidArgumentException
     */
    public function get($key, $default = null)
    {
        self::validateKey($key);

        $this->queriesCount++;

        return ($tmp = $this->memcache->get($key)) === false
            ? false
            : unserialize($tmp);
    }

    /**
     * @param string $key    for data
     * @param mixed  $value  any type of supported data: object, string, int…
     * @param int    $expire Expiration time of the item (usually in seconds).
     *                       If it's equal to null, the 'defaultExpiration' config value will used.
     *                       If it's equal to zero, the item will never expire.
     *                       You can also use Unix timestamp or a number of seconds starting from current time,
     *                       but in the latter case the number of seconds may not exceed 2592000 (30 days).
     *
     * @return bool returns <b>TRUE</b> on success or <b>FALSE</b> on failure
     *
     * @throws \Colibri\Cache\Storage\Exception\InvalidArgumentException
     */
    public function set($key, $value, $expire = null)
    {
        self::validateKey($key);

        $this->queriesCount++;

        return $this->memcache->set(
            $key, serialize($value), 0, $expire !== null ? $expire : self::$defaultExpiration
        );
    }

    /**
     * @param string $key
     *
     * @return bool true on success and false on fail
     *
     * @throws \Colibri\Cache\Storage\Exception\InvalidArgumentException
     */
    public function delete($key)
    {
        self::validateKey($key);

        $this->queriesCount++;

        return $this->memcache->delete($key);
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        return $this->memcache->flush();
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as
     *                  value.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null)
    {
        if ( ! (is_array($keys) || $keys instanceof \Traversable)) {
            throw new Exception\InvalidArgumentException('Parameter `$keys` must be iterable (array or Traversable).');
        }
        foreach ($keys as $key) {
            self::validateKey($key);
        }

        return array_fill_keys($keys, $default) + $this->get((array)$keys);
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable               $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Colibri\Cache\Storage\Exception\InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null)
    {
        $failed    = false;
        $exception = null;
        foreach ($values as $key => $value) {
            try {
                $failed = $failed || $this->set($key, $value, $ttl);
            } catch (InvalidArgumentException $exception) {
            }
        }

        if ($exception !== null) {
            throw $exception;
        }

        return ! $failed;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys)
    {
        $failed = false;
        foreach ($keys as $key) {
            $failed = $failed || $this->delete($key);
        }

        return ! $failed;
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException if the $key string is not a legal value.
     */
    public function has($key)
    {
        return $this->get($key) !== false;
    }

    /**
     * @param string $key
     *
     * @throws \Colibri\Cache\Storage\Exception\InvalidArgumentException
     */
    private static function validateKey(string $key)
    {
        if (preg_match('/\s/', $key)) {
            throw new InvalidArgumentException('Key can`t contains any whitespace chars: spaces, tabs, new-line...');
        }
    }
}
