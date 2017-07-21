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
     * @param string $type one of 'alnum', 'numeric', 'nozero', 'unique', 'guid'
     * @param int    $len  length of generated string
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function random($type = 'alnum', $len = 8)
    {
        switch ($type) {
            case 'alnum':
            case 'numeric':
            case 'nozero':
                switch ($type) {
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool = '0123456789';
                        break;
                    case 'nozero':
                        $pool = '123456789';
                        break;
                }
                $str = '';
                for ($i = 0; $i < $len; $i++) {
                    /* @noinspection PhpUndefinedVariableInspection */
                    $str .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
                }

                return $str;
            case 'unique':
                return md5(uniqid(mt_rand()));
            case 'guid':
                return self::generateGUID();
            default:
                throw new \Exception('unknown random type');
        }
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
     * @param string $str
     * @param string $beginsWith
     *
     * @return bool
     */
    public static function beginsWith($str, $beginsWith)
    {
        return substr($str, 0, strlen($beginsWith)) === $beginsWith;
    }

    /**
     * Checks if string ends with specified part.
     *
     * @param string $str
     * @param string $endsWith
     *
     * @return bool
     */
    public static function endsWith($str, $endsWith)
    {
        return substr($str, -strlen($endsWith)) === $endsWith;
    }

    /**
     * Checks if string contains specified $subString.
     *
     * @param string $str
     * @param string $subString
     *
     * @return bool
     */
    public static function contains($str, $subString)
    {
        return strpos($str, $subString) !== false;
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
        if ( ! ctype_upper($str)) {
            $str = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1' . $delimiter, $str));
            $str = preg_replace('/\s+/', '', $str);
        }

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
}
