<?php

namespace Colibri\Util;

use Exception;

class Str
{

    /**
     * Validate email. Not work with not latin chars.
     *
     * @param $value
     *
     * @return bool
     */
    public static function isEmail($value)
    {
        return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Generate random string.
     *
     * @param string $type
     * @param int $len
     *
     * @return string
     * @throws Exception
     */
    public static function random($type = 'alnum', $len = 8)
    {
        $pool = null;
        $result = null;

        switch ($type) {
            case 'alnum':
            case 'numeric':
            case 'nozero':
                switch ($type) {
                    case 'alnum':
                        $pool='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool='0123456789';
                        break;
                    case 'nozero':
                        $pool='123456789';
                        break;
                }

                $result = '';
                for ($i = 0; $i < $len; $i++) {
                    $result .= substr($pool, mt_rand(0, strlen($pool) - 1), 1);
                }
                break;
            case 'unique':
                $result = md5(uniqid(mt_rand()));
                break;
            case 'quid':
                $result = self::generateGUID();
                break;
            default:
                throw new Exception('Invalid type of random string.');
        }

        return $result;
    }

    /**
     * Generate GUID.
     *
     * @return string
     */
    public static function generateGUID()
    {
        $guid = "";

        for ($i = 1; $i <= 16; $i++)
        {
            $b = (int) rand(0, 0xff);

            switch ($i) {
                case 7:
                    $b &= 0x0f;
                    $b |= 0x40;
                    $guid = sprintf("%02s", base_convert($b,10,16));
                    break;
                case 9:
                    $b &= 0x3f;
                    $b |= 0x80;
                    $guid = sprintf("%02s", base_convert($b,10,16));
                    break;
                case 4:
                case 6:
                case 8:
                case 10:
                    $guid .= '-';
                    break;
            }
        }

        return $guid;
    }

	/**
	 * @param string $str
	 * @return bool
	 */
static
	public		function	isInt($str)
	{
		return is_int($str) || $str === (string)(int)$str;
	}

	/**
	 * @param $str
	 * @param $beginsWith
	 *
	 * @return bool
	 */
static
	public		function	beginsWith($str, $beginsWith)
	{
		return substr($str, 0, strlen($beginsWith)) === $beginsWith;
	}
static
	public      function    endsWith($str, $endsWith)
	{
		return substr($str, -strlen($endsWith)) === $endsWith;
	}
static
	public		function	contains($str,$substr)
	{
		return strpos($str,$substr)!==false;
	}

	/**
	 *
	 * @param string $str
	 * @return bool
	 */
static
	public		function	isJSON($str)
	{
		return json_decode($str)!==null;
	}

	/**
	 * @param string $str
	 * @param string $delimiter
	 *
	 * @return string
	 */
static
	public		function	firstPart($str, $delimiter = ' ')
	{
		return array_shift(explode($delimiter, $str));
	}

	/**
	 * @param string $str
	 * @param string $delimiter
	 *
	 * @return string
	 */
static
	public		function	lastPart($str, $delimiter = ' ')
	{
		return array_pop(explode($delimiter, $str));
	}

	/**
	 * @param string $str
	 * @param string $delimiter
	 *
	 * @return string
	 */
static
	public		function	snake($str, $delimiter = '_')
	{
		if (!ctype_upper($str)) {
			$str = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1' . $delimiter, $str));
			$str = preg_replace('/\s+/', '', $str);
		}

		return $str;
	}
}
