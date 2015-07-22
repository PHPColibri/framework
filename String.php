<?php
namespace Colibri\Util;
/**
 *
 * @author		Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package		xTeam
 * @subpackage	a13FW
 * @version		1.00.0
 */
class String
{
	/**
	 *
	 * @param string $str
	 * @return bool
	 */
static
	public		function	isEmail($str)
	{
		return (bool)preg_match(RegExp::isEmail,$str);
	}
static
	public		function	random($type='alnum',$len=8)
	{
		switch ($type)
		{
			case 'alnum':
			case 'numeric':
			case 'nozero':
					switch ($type)
					{
						case 'alnum':	$pool='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';break;
						case 'numeric':	$pool='0123456789';break;
						case 'nozero':	$pool='123456789';break;
					}
					$str = '';
					for ($i=0;$i<$len;$i++)
						$str.=substr($pool,mt_rand(0,strlen($pool)-1),1);
					return $str;
			case 'unique' : return md5(uniqid(mt_rand()));
			case 'guid'   : return self::generateGUID();
		}
	}
	/**
	 * @return string GUID
	 */
static
	public		function	generateGUID()
	{
		$guidstr = "";
		for ($i=1;$i<=16;$i++)
		{
			$b = (int)rand(0,0xff);
			if ($i==7) { $b&=0x0f; $b|=0x40; } // version 4 (random)
			if ($i==9) { $b&=0x3f; $b|=0x80; } // variant
			$guidstr.=sprintf("%02s", base_convert($b,10,16));
			if ($i==4 || $i==6 || $i==8 || $i==10) { $guidstr.='-'; }
		}
		return $guidstr;
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
