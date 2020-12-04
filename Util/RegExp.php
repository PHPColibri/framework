<?php
namespace Colibri\Util;

use RuntimeException;

/**
 * RegExp popular patterns.
 */
class RegExp
{
    public const IS_DATE = '/^[0-9]{4}-(((0[13578]|(10|12))-(0[1-9]|[1-2][0-9]|3[0-1]))|(02-(0[1-9]|[1-2][0-9]))|((0[469]|11)-(0[1-9]|[1-2][0-9]|30)))$/';

    public const IS_EMAIL = '/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD';

    /**
     * Returns array of found matches or empty array.
     * If error occurred, throws RuntimeException with preg_last_error_msg(), preg_last_error()
     *
     * @param string $pattern
     * @param string $subject
     *
     * @return array
     *
     * @throws RuntimeException
     */
    public static function findAll(string $pattern, string $subject): array
    {
        $found = preg_match_all($pattern, $subject, $matches);
        if ($found === false) {
            throw new RuntimeException(preg_last_error_msg(), preg_last_error());
        }

        return $found ? Arr::transpose($matches) : [];
    }

    /**
     * Returns found matches or null.
     * If error occurred, throws RuntimeException with preg_last_error_msg(), preg_last_error()
     *
     * @param string $pattern
     * @param string $subject
     *
     * @return array|null
     *
     * @throws RuntimeException
     */
    public static function findFirst(string $pattern, string $subject): ?array
    {
        $found = preg_match($pattern, $subject, $matches);
        if ($found === false) {
            throw new RuntimeException(preg_last_error_msg(), preg_last_error());
        }

        return $found ? $matches : null;
    }
}
