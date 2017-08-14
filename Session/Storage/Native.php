<?php
namespace Colibri\Session\Storage;

use Colibri\Pattern\Singleton;
use Colibri\Session\Exception;
use Colibri\Util\Arr;

/**
 * Class Native.
 */
class Native extends Singleton implements StorageInterface
{
    /**
     * Session Native driver constructor.
     */
    protected function __construct()
    {
        if ( ! session_start()) {
            throw new Exception('can`t start session');
        }
    }

    /**
     * @param string $dottedKey
     *
     * @return bool
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

    /**
     * Closes current session and try to find and open new with <$sessionId>.
     *
     * @param string $id
     * @param bool   $saveCurrent
     *
     * @throws \Colibri\Session\Exception
     */
    public function catch($id, $saveCurrent = true)
    {
        if ($saveCurrent) {
            session_write_close();
        } else {
            session_abort();
        }

        if (session_id($id) === '')
            throw new Exception("can`t find session with id $id");
        if ( ! session_start()) {
            throw new Exception('can`t start session');
        }
    }
}
