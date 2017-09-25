<?php
namespace Colibri\Cache\Storage;

/**
 * Class AbstractStorage.
 */
abstract class AbstractStorage implements StorageInterface
{
    /**
     * @var int count of queries to Cache
     */
    protected $queriesCount = 0;

    /**
     * @return int
     */
    public function getQueriesCount(): int
    {
        return $this->queriesCount;
    }

    /**
     * @param string   $key
     * @param \Closure $getValueCallback
     * @param int|null $expire
     *
     * @return mixed
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function remember(string $key, \Closure $getValueCallback, int $expire = null)
    {
        if (($fromCache = $this->get($key)) !== false) {
            return $fromCache;
        }

        $this->set($key, $value = $getValueCallback(), $expire);

        return $value;
    }
}
