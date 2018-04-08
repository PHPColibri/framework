<?php
namespace Colibri\Cache;

/**
 * Cache interface.
 */
interface CacheInterface
{
    /**
     * @return int
     */
    public static function getQueriesCount(): int;

    /**
     * @param string $key for data
     *
     * @return mixed returns cached data
     */
    public static function get(string $key);

    /**
     * @param string   $key    key for data
     * @param mixed    $value  any type of supported data: object, string, int…
     * @param int|null $expire seconds
     *
     * @return bool true on success and false on failure
     */
    public static function set(string $key, $value, int $expire = null): bool;

    /**
     * @param string $key key for data
     *
     * @return bool true on success and false on failure
     */
    public static function delete(string $key): bool;

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
    public static function remember(string $key, \Closure $getValueCallback, int $expire = null);

    /**
     * Аннулирует все записи в кеше.
     * Clears all cached values.
     */
    public static function flush();
}
