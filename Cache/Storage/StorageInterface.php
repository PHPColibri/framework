<?php
namespace Colibri\Cache\Storage;

use Psr\SimpleCache\CacheInterface;

/**
 * Interface StorageInterface for Session Storage drivers.
 */
interface StorageInterface extends CacheInterface
{
    /**
     * @return int
     */
    public function getQueriesCount(): int;

    /**
     * @param string   $key
     * @param \Closure $getValueCallback
     * @param int|null $expire
     *
     * @return mixed returns cached data
     */
    public function remember(string $key, \Closure $getValueCallback, int $expire = null);
}
