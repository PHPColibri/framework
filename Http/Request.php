<?php
namespace Colibri\Http;

use Colibri\Pattern\Helper;
use Colibri\Util\Str;

class Request extends Helper
{
    /**
     * @param string $uri
     *
     * @return bool
     */
    public static function beginsWith($uri)
    {
        return Str::beginsWith($_SERVER['REQUEST_URI'], $uri);
    }

    /**
     * @param string $uri
     *
     * @return bool
     */
    public static function is($uri)
    {
        return $_SERVER['PATH_INFO'] == $uri;
    }
}