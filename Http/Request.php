<?php
namespace Colibri\Http;

use Colibri\Pattern\Helper;
use Colibri\Util\Str;

/**
 * Request helper.
 */
class Request extends Helper
{
    /**
     * Tests if requested uri begins with specified string.
     *
     * @param string $uriPart
     *
     * @return bool
     */
    public static function beginsWith($uriPart)
    {
        return Str::beginsWith($_SERVER['REQUEST_URI'], $uriPart);
    }

    /**
     * Tests if requested uri is exactly specified string.
     *
     * @param string $uri
     *
     * @return bool
     */
    public static function is($uri)
    {
        return $_SERVER['PATH_INFO'] == $uri;
    }
}
