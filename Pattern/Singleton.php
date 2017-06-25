<?php
namespace Colibri\Pattern;

/**
 * Represent a singleton pattern:
 *   not public __construct, __clone & __wakeup,
 *   implement ::getInstance().
 */
abstract class Singleton
{
    /**
     * @var static
     */
    protected static $instance = null;

    /**
     * Singleton constructor.
     */
    abstract protected function __construct();

    /**
     * Close public access.
     */
    private function __clone()
    {
    }

    /**
     * Close public access/.
     */
    private function __wakeup()
    {
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        return static::$instance === null
            ? static::$instance = new static()
            : static::$instance;
    }
}
