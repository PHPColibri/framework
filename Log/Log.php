<?php
namespace Colibri\Log;

use Colibri\Config\Config;
use Colibri\Pattern\Helper;
use Colibri\Util\Arr;

/**
 * Simple Log.
 */
class Log extends Helper
{
    /**
     * @var array default config values, if no one specified
     */
    protected static $defaultConfig = [
        'folder' => '/var/log/colibri',
        'prefix' => 'colibri',
    ];
    /**
     * @var array real laded config
     */
    protected static $config = null;

    /**
     * @param string $message       message to log
     * @param string $who           log name
     * @param bool   $logServerVars log or not additional info ($_GET, $_POST, $_SESSION, $_COOKIE)
     *
     * @return bool
     * @throws \InvalidArgumentException if can`t get the real-path of log config file
     */
    public static function add($message, $who = 'colibri', $logServerVars = false)
    {
        $ret = "\n" . '### ' . date('d-m-y H:i:s') . ' ### ------------------------------------------------------------------------------------------' . "\n";
        $ret .= "\n" . $message . "\n";
        if ($logServerVars) {
            $ret .= "\$_GET:\n" . var_export($_GET, true);
            $ret .= "\$_POST:\n" . var_export($_POST, true);
            if (isset($_SESSION)) {
                $ret .= "\$_SESSION\n" . var_export($_SESSION, true);
            }
            $ret .= "\$_COOKIE\n" . var_export($_COOKIE, true) . "\n";
        }
        $ret .= '---------------------------------------------------------------------------------------------------------------- ###' . "\n";

        return self::write2file($ret, $who);
    }

    /**
     * @param string $message message to log
     * @param string $who     log name
     *
     * @return bool TRUE on success, FALSE on fail
     * @throws \InvalidArgumentException if can`t get the real-path of log config file
     */
    public static function warning($message, $who = 'colibri')
    {
        $message = '### ' . date('d-m-y H:i:s') . ' ###: ' . $message . "\n";

        return self::write2file($message, $who);
    }

    /**
     * @param $message
     * @param $who
     *
     * @return bool
     * @throws \InvalidArgumentException if can`t get the real-path of log config file
     */
    private static function write2file($message, $who)
    {
        if (static::$config === null) {
            static::loadFromConfig();
        }

        if ( ! file_exists(self::$config['folder'])) {
            if ( ! mkdir(self::$config['folder'], 0777, true)) { // 0777 - just default value, which means that need to use umask()
                return false;
            }
        }
        $filename = self::$config['folder'] . '/' . self::$config['prefix'] . '.' . $who . '.log';

        return self::fwrite($filename, $message);
    }

    /**
     * @param string $filename
     * @param string $str
     *
     * @return bool
     */
    private static function fwrite($filename, $str)
    {
        $f = @fopen($filename, 'a+');
        if ( ! $f) {
            return false;
        }
        fwrite($f, $str);
        fclose($f);

        return true;
    }

    /**
     * @return array
     * @throws \InvalidArgumentException if can`t get the real-path of log config file
     */
    private static function loadFromConfig()
    {
        static::$config           = Arr::overwrite(
            static::$defaultConfig,
            Config::getOrEmpty('log')
        );
        static::$config['folder'] = rtrim(static::$config['folder'], '/\\ ');

        return static::$config;
    }
}
