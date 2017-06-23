<?php
namespace Colibri\Session\Storage;

use Colibri\Pattern\Singleton;
use Colibri\Util\Arr;

/**
 * Class Native
 *
 * @package Colibri\Session\Storage
 */
class Native extends Singleton implements StorageInterface
{
    /**
     */
    protected function __construct()
    {
        session_start();
    }

    /**
     * @param string $dottedKey
     *
     * @return mixed
     */
    public function has($dottedKey)
    {
        return Arr::get($_SESSION, $dottedKey, '~no~value~in~storage~') === '~no~value~in~storage~';
    }

    /**
     * @param string $dottedKey
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($dottedKey, $default = null)
    {
        return Arr::get($_SESSION, $dottedKey, $default);
    }

    /**
     * @param string $dottedKey
     * @param mixed  $value
     *
     * @return mixed
     */
    public function set($dottedKey, $value)
    {
        return Arr::set($_SESSION, $dottedKey, $value);
    }

    /**
     * @param string $dottedKey
     *
     * @return mixed|null returns removed value or null if key not found
     */
    public function remove($dottedKey)
    {
        return Arr::remove($_SESSION, $dottedKey);
    }
}