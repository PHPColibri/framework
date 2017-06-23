<?php
namespace Colibri\Cache;

/**
 *
 * @author         Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package        xTeam
 * @subpackage     a13FW
 * @version        1.0.0
 */
interface ICache
{
    /**
     * @param string $key for data
     *
     * @return boolean result OR data
     */
    static
    public function get($key);

    /**
     * @param string $key    for data
     * @param mixed  $value  any type of supported data: object, string, int…
     * @param int    $expire seconds
     *
     * @return boolean result
     */
    static
    public function set($key, $value, $expire = null);

    /**
     * @param    string $key for data
     */
    static
    public function delete($key);

    /**
     * @param string   $key for data
     * @param \Closure $getValueCallback
     * @param null     $expire
     *
     * @return bool result OR data
     */
    static
    public function remember($key, \Closure $getValueCallback, $expire = null);
}
