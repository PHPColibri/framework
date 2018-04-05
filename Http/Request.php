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
        return $_SERVER['REQUEST_URI'] === $uri;
    }

    /**
     * @param int $mainDomainLevel
     *
     * @return null|string
     */
    public static function domainPrefix($mainDomainLevel = 2)
    {
        static $domainPrefix = null;

        return $domainPrefix ?? $domainPrefix = self::retrieveDomainPrefix($mainDomainLevel);
    }

    /**
     * @param int $mainDomainLevel
     *
     * @return string
     */
    private static function retrieveDomainPrefix(int $mainDomainLevel): string
    {
        $host  = $_SERVER['HTTP_HOST'];
        $parts = explode('.', $host);
        for ($i = 0; $i < $mainDomainLevel; $i++) {
            array_pop($parts);
        }

        return implode('.', $parts);
    }
}
