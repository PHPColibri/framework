<?php
namespace Colibri\Cache;

/**
 * Cache interface.
 */
interface ICache
{
    /**
     * @param string $key for data
     *
     * @return boolean result OR data
     */
    public static function get($key);

    /**
     * @param string $key    key for data
     * @param mixed  $value  any type of supported data: object, string, int…
     * @param int    $expire seconds
     *
     * @return boolean result
     */
    public static function set($key, $value, $expire = null);

    /**
     * @param string $key key for data
     */
    public static function delete($key);

    /**
     * Tries to ::get() data from cache; and if not exists,
     *   get the real date through $getValueCallback()
     *   and store it in cache, than return.
     *
     * @param string   $key              key for data
     * @param \Closure $getValueCallback closure that get the real (not cached) value
     * @param null     $expire           seconds
     *
     * @return bool result OR data
     */
    public static function remember($key, \Closure $getValueCallback, $expire = null);
}
