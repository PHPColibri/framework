<?php
namespace Colibri\Log;

use Colibri\Pattern\Helper;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Simple Log.
 *
 * @method static LoggerInterface notFound()
 */
class Log extends Helper
{
    /**
     * @var array
     */
    protected static $config = [
        'error'    => ['handler' => ['class' => StreamHandler::class,],],
        'notFound' => ['handler' => ['class' => StreamHandler::class,],],
        'folder'   => '/var/log/colibri',
        'prefix'   => 'colibri',
    ];
    /**
     * @var LoggerInterface[]
     */
    protected static $logger = [];

    /**
     * @param array $config
     */
    public static function setConfig(array $config)
    {
        self::$config = array_replace_recursive(self::$config, $config);
    }

    /**
     * @param string $name
     *
     * @return \Psr\Log\LoggerInterface
     */
    final protected static function logger($name = 'error'): LoggerInterface
    {
        return self::$logger[$name] ?? self::$logger[$name] = self::createLogger($name);
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return \Psr\Log\LoggerInterface
     */
    final public static function __callStatic(string $name, array $arguments): LoggerInterface
    {
        return static::logger($name);
    }

    /**
     * @param string $name
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected static function createLogger(string $name): LoggerInterface
    {
        $config       = static::$config;
        $loggerConfig = $config[$name];
        $handler      = $loggerConfig['handler']['class'];
        $params       = $loggerConfig['handler']['params'] ?? [];

        array_unshift($params, "{$config['folder']}/{$config['prefix']}.$name.log");

        return (new Logger($name))->pushHandler(new $handler(...$params));
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function emergency($message, array $context = [])
    {
        static::logger()->emergency($message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function alert($message, array $context = [])
    {
        static::logger()->alert($message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function critical($message, array $context = [])
    {
        static::logger()->critical($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function error($message, array $context = [])
    {
        static::logger()->error($message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function warning($message, array $context = [])
    {
        static::logger()->warning($message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function notice($message, array $context = [])
    {
        static::logger()->notice($message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function info($message, array $context = [])
    {
        static::logger()->info($message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function debug($message, array $context = [])
    {
        static::logger()->debug($message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param string $level one of LogLevel::<CONST>-ants
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public static function log($level, $message, array $context = [])
    {
        static::logger()->log($level, $message, $context);
    }
}
