<?php
namespace Colibri\Session\Storage;

/**
 * Interface StorageInterface for Session Storage drivers.
 */
interface StorageInterface
{
    /**
     * @param string $dottedKey
     *
     * @return bool
     */
    public function has($dottedKey);

    /**
     * @param string $dottedKey
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($dottedKey, $default = null);

    /**
     * @param string $dottedKey
     * @param mixed  $value
     *
     * @return mixed
     */
    public function set($dottedKey, $value);

    /**
     * @param string $dottedKey
     *
     * @return mixed|null returns removed value or null if key not found
     */
    public function remove($dottedKey);

    /**
     * Closes current session and try to find and open new with <$sessionId>.
     *
     * @param string $id
     * @param bool   $saveCurrent
     *
     * @throws \Colibri\Session\Exception
     */
    public function catch($id, $saveCurrent);
}
