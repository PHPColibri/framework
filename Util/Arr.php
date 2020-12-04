<?php
namespace Colibri\Util;

use Colibri\Pattern\Helper;

/**
 * Array manipulation helper.
 */
class Arr extends Helper
{
    /**
     * Overwrites values of the $array with values from $with array.
     *
     * @param array $array
     * @param array $with
     *
     * @return array
     */
    public static function overwrite(array $array, array $with)
    {
        return array_replace_recursive($array, $with);
    }

    /**
     * Gets value from $array using "dot" notation, or returns $default value.
     *
     * @param array  $array
     * @param string $dottedKey
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function get(array $array, string $dottedKey = null, $default = null)
    {
        if ($dottedKey === null) {
            return $array;
        }
        if (isset($array[$dottedKey])) {
            return $array[$dottedKey];
        }

        foreach (explode('.', $dottedKey) as $segment) {
            if ( ! is_array($array) || ! array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Sets $value to $array using "dot" notation.
     *
     * @param array  $array
     * @param string $dottedKey
     * @param mixed  $value
     *
     * @return mixed
     */
    public static function &set(array &$array, string $dottedKey, $value)
    {
        if ($dottedKey === null) {
            return $array = $value;
        }

        if (isset($array[$dottedKey])) {
            $array[$dottedKey] = $value;

            return $array;
        }

        $keyParts = explode('.', $dottedKey, 2);
        if ( ! isset($array[$keyParts[0]])) {
            $array[$keyParts[0]] = [];
        }

        $array[$keyParts[0]] = count($keyParts) === 2
            ? self::set($array[$keyParts[0]], $keyParts[1], $value)
            : $value;

        return $array;
    }

    /**
     * Removes value from $array.
     *
     * @param array  $array
     * @param string $dottedKey
     *
     * @return mixed|null returns removed value or null if key not found
     */
    public static function remove(array &$array, string $dottedKey)
    {
        if (isset($array[$dottedKey])) {
            $value = $array[$dottedKey];
            unset($array[$dottedKey]);

            return $value;
        }

        $k = explode('.', $dottedKey, 2);

        return isset($k[1])
            ? self::remove($array[$k[0]], $k[1])
            : null;
    }

    /**
     * Returns only specified by $keys values from $array.
     *
     * @param array $array
     * @param array $keys
     *
     * @return array
     */
    public static function only(array $array, array $keys)
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * in_array() alias.
     *
     * @param array $array
     * @param mixed $value
     *
     * @return bool
     */
    public static function contains(array $array, $value): bool
    {
        return in_array($value, $array);
    }

    /**
     * @param array $array
     * @param int   $count
     *
     * @return array
     */
    public static function last(array $array, int $count = 1): array
    {
        return array_slice($array, -$count);
    }

    /**
     * Transpose. Flips diagonally.
     *
     * @param array $array
     *
     * @return array
     */
    public static function transpose(array $array): array
    {
        return array_map(null, ...$array);
    }
}
