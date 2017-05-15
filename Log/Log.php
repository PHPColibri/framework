<?php

namespace Colibri\Log;

use Colibri\Pattern\Helper;
use Colibri\Config\Config;
use Colibri\Util\Arr;

/**
 * Description of Log
 *
 * @author		Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package		xTeam
 * @subpackage	a13FW
 * @version		1.00.1
 */
class Log extends Helper
{
	protected static $defaultConfig = array(
		'folder' => '/var/log/colibri',
		'prefix' => 'colibri',
	);
	protected static $config = null;
//	public static $logFolder = '/var/log/colibri';
//	public static $prefix	 = 'colibri';

	// TODO [alek13]:
	//public	static	$template='';

	public static function add($message, $who = 'colibri', $logServerVars = false)
	{
		$ret = "\n" . '### ' . date('d-m-y H:i:s') . ' ### ------------------------------------------------------------------------------------------' . "\n";
		$ret.="\n" . $message . "\n";
		if ($logServerVars) {
			$ret.="\$_GET:\n" . var_export($_GET, true);
			$ret.="\$_POST:\n" . var_export($_POST, true);
			if (isset($_SESSION))
				$ret.="\$_SESSION\n" . var_export($_SESSION, true);
			$ret.="\$_COOKIE\n" . var_export($_COOKIE, true) . "\n";
		}
		$ret.='---------------------------------------------------------------------------------------------------------------- ###' . "\n";

		return self::write2file($ret, $who);
	}

	public static function warning($message, $who = 'colibri')
	{
		$message = '### ' . date('d-m-y H:i:s') . ' ###: ' . $message . "\n";

		return self::write2file($message, $who);
	}

	private static function write2file($message, $who)
	{
		if (static::$config === null)
			static::loadFromConfig();

		if (!file_exists(self::$config['folder']))
			if (!mkdir(self::$config['folder'], 0777, true)) // 0777 - just default value, which means that need to use umask()
				return false;
		$filename = self::$config['folder'] . '/' . self::$config['prefix'] . '.' . $who . '.log';

		return self::fwrite($filename, $message);
	}

	private static function fwrite($filename, $str)
	{
		$f = @fopen($filename, 'a+');
		if (!$f)
			return false;
		fwrite($f, $str);
		fclose($f);
		return true;
	}

	private static function loadFromConfig()
	{
		static::$config = Arr::overwrite(
			static::$defaultConfig,
			Config::getOrEmpty('log')
		);
		static::$config['folder'] = rtrim(static::$config['folder'], '/\\ ');
		
		return static::$config;
	}

}
