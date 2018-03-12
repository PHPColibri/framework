<?php
namespace Colibri\Application\Error;

use Colibri\Config\Config;
use Colibri\Log\Log;
use Colibri\Util\Html;

class Handler
{
    /**
     */
    public static function register()
    {
        set_error_handler([Handler::class, 'errorHandler'], -1);
        set_exception_handler([Handler::class, 'exceptionHandler']);
        register_shutdown_function([Handler::class, 'shutdownHandler']);
    }

    /**
     * @param int    $severity
     * @param string $message
     * @param string $file
     * @param int    $line
     *
     * @throws \ErrorException
     */
    public static function errorHandler(int $severity, string $message, string $file, int $line)
    {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * @param \Throwable $throwable
     */
    public static function exceptionHandler(\Throwable $throwable)
    {
        self::showError($throwable);

        Log::add($throwable, 'core.module');
    }

    /**
     *
     */
    public static function shutdownHandler()
    {
        if (!($error = error_get_last())) {
            return;
        }

        self::exceptionHandler(
            new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line'])
        );
    }

    /**
     * @param \Throwable $throwable
     * @param int        $code
     * @param string     $text
     */
    public static function showError(\Throwable $throwable, int $code = 500, $text = 'Internal Server Error')
    {
        if (Config::application('debug')) {
            /* @noinspection PhpUnusedLocalVariableInspection variable uses in 500.php */
            $error = Html::e($throwable);
        }

        header("HTTP/1.1 $code $text");

        $file = $code . '.php';
        $path = __DIR__ . "/../../../../../application/templates/";
        /** @noinspection PhpIncludeInspection */
        include file_exists($path . $file) ? $path . $file : __DIR__ . '/views/' . $file;
    }
}