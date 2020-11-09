<?php
namespace Colibri\Util;

use Colibri\Pattern\Helper;

/**
 * With string manipulations methods.
 */
class Str extends Helper
{
    /**
     * Checks if string stores an email.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function isEmail($str)
    {
        return (bool)preg_match(RegExp::IS_EMAIL, $str);
    }

    /**
     * Generates random string.
     *
     * @param string $type   one of 'alnum', 'numeric', 'nozero', 'unique', 'guid'
     * @param int    $length length of generated string
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function random($type = 'alnum', $length = 8)
    {
        switch ($type) {
            case 'alnum':
            case 'numeric':
            case 'nozero':
                $pool = self::getPoolForRandom($type);

                return static::randomFrom($pool, $length);
            case 'unique':
                return md5(uniqid(mt_rand(), true));
            case 'guid':
                return self::generateGUID();
            default:
                throw new \InvalidArgumentException('unknown random type');
        }
    }

    /**
     * @param string $type
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private static function getPoolForRandom(string $type): string
    {
        switch ($type) {
            case 'alnum':
                return '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            case 'numeric':
                return '0123456789';
            case 'nozero':
                return '123456789';
            default:
                throw new \InvalidArgumentException('unknown random type');
        }
    }

    /**
     * @param string $pool
     * @param int    $length
     *
     * @return string
     */
    public static function randomFrom(string $pool, $length = 8): string
    {
        $str        = '';
        $poolLength = strlen($pool);
        for ($i = 0; $i < $length; $i++) {
            $str .= $pool[mt_rand(0, $poolLength - 1)];
        }

        return $str;
    }

    /**
     * Generates GUID.
     *
     * @return string GUID
     */
    public static function generateGUID()
    {
        $guidStr = '';
        for ($i = 1; $i <= 16; $i++) {
            $b = (int)mt_rand(0, 0xff);
            if ($i == 7) {
                $b &= 0x0f;
                $b |= 0x40;
            } // version 4 (random)
            if ($i == 9) {
                $b &= 0x3f;
                $b |= 0x80;
            } // variant
            $guidStr .= sprintf('%02s', base_convert($b, 10, 16));
            if ($i == 4 || $i == 6 || $i == 8 || $i == 10) {
                $guidStr .= '-';
            }
        }

        return $guidStr;
    }

    /**
     * Checks if string stores an integer.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function isInt($str)
    {
        return is_int($str) || $str === (string)(int)$str;
    }

    /**
     * Checks if string begins with specified part.
     *
     * @param string          $str
     * @param string|string[] $beginsWith
     *
     * @return bool
     */
    public static function beginsWith($str, $beginsWith)
    {
        if ( ! is_array($beginsWith)) {
            return substr($str, 0, strlen($beginsWith)) === $beginsWith;
        }

        foreach ($beginsWith as $needle) {
            if (static::beginsWith($str, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if string ends with specified part.
     *
     * @param string          $str
     * @param string|string[] $endsWith
     *
     * @return bool
     */
    public static function endsWith($str, $endsWith)
    {
        if ( ! is_array($endsWith)) {
            return substr($str, -strlen($endsWith)) === $endsWith;
        }

        foreach ($endsWith as $needle) {
            if (static::endsWith($str, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if string contains specified $subString.
     *
     * @param string          $str
     * @param string|string[] $subString
     *
     * @return bool
     */
    public static function contains($str, $subString)
    {
        if ( ! is_array($subString)) {
            return strpos($str, $subString) !== false;
        }

        foreach ($subString as $needle) {
            if (static::contains($str, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if string stores a JSON.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function isJSON($str)
    {
        return json_decode($str) !== null;
    }

    /**
     * Gets the first part of string divided by $delimiter.
     *
     * @param string $str
     * @param string $delimiter
     *
     * @return string
     */
    public static function firstPart($str, $delimiter = ' ')
    {
        $parts = explode($delimiter, $str);

        return array_shift($parts);
    }

    /**
     * Gets the last part of string divided by $delimiter.
     *
     * @param string $str
     * @param string $delimiter
     *
     * @return string
     */
    public static function lastPart($str, $delimiter = ' ')
    {
        $parts = explode($delimiter, $str);

        return array_pop($parts);
    }

    /**
     * Converts string into snaked style.
     *
     * @param string $str
     * @param string $delimiter
     *
     * @return string
     */
    public static function snake($str, $delimiter = '_')
    {
        $str = preg_replace('/([A-Z ])/', ' $1', $str);
        $str = preg_replace('/\s+/', $delimiter, trim($str));
        $str = strtolower($str);

        return $str;
    }

    /**
     * Converts string into studly case style.
     *
     * @param string $str
     *
     * @return string
     */
    public static function studly($str)
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $str)));
    }

    /**
     * Converts string into studly case style.
     *
     * @param string $str
     *
     * @return string
     */
    public static function camel($str)
    {
        return lcfirst(static::studly($str));
    }

    /**
     * Gets specified $i-th part of string divided by $delimiter, or returns $default.
     *
     * @param string $str
     * @param int    $i
     * @param string $delimiter
     * @param string $default
     *
     * @return string
     */
    public static function part($str, $i, $delimiter, $default = null)
    {
        $parts = explode($delimiter, $str);

        return isset($parts[$i])
            ? $parts[$i]
            : $default;
    }

    /**
     * Gets specified $i-th word of string divided by $delimiter, or returns $default.
     * Same as ::part() with ' '(space) delimiter.
     *
     * @param      $str
     * @param      $i
     * @param null $default
     *
     * @return string
     */
    public static function word($str, $i, $default = null)
    {
        return static::part($str, $i, ' ', $default);
    }

    /**
     * Checks if string contains any digit or not.
     *
     * @param $str
     *
     * @return bool
     */
    public static function hasDigits($str)
    {
        return strpbrk($str, '0123456789') !== false;
    }

    /**
     * Cuts specified $cut substrings from a given $str.
     *
     * @param string $str
     * @param string $cut
     *
     * @return string
     */
    public static function cut(string $str, string $cut): string
    {
        return str_replace($cut, '', $str);
    }

    /**
     * Replaces all $vars `{key}`s to theirs `values` in a given $str.
     * For example,
     *   `Str::build('{scheme}://{domain}', ['scheme' => 'https', 'domain' => 'phpcolibri.com'])`
     *   returns `https://phpcolibri.com`.
     *
     * @param string $str
     * @param array  $vars
     * @param string $tags
     *
     * @return string
     */
    public static function build(string $str, array $vars, string $tags = '{}'): string
    {
        $open  = $tags[0] ?? '';
        $close = $tags[1] ?? '';
        foreach ($vars as $key => $value) {
            $search = $open . $key . $close;
            $str    = str_replace($search, $value, $str);
        }

        return $str;
    }
}
