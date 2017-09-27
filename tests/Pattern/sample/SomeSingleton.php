<?php
namespace Colibri\tests\Pattern\sample;

use Colibri\Pattern\Singleton;

/**
 * Class SomeSingleton
 *
 * @package Colibri\tests\Pattern\sample
 */
class SomeSingleton extends Singleton
{
    /**
     * @var static
     */
    protected static $instance = null;

    /**
     * Singleton constructor.
     */
    protected function __construct()
    {
    }
}
