<?php
namespace Colibri\Pattern;

/**
 * Represent a helper pattern:
 *   not public __construct.
 */
abstract class Helper
{
    /**
     * Close public access to constructor.
     *
     * @codeCoverageIgnore
     */
    final private function __construct()
    {
    }
}
