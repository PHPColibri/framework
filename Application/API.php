<?php
namespace Colibri\Application;

use Colibri\Cache\Cache;
use Colibri\Config\Config;
use Colibri\Session\Session;

/**
 * Application API for most common functionality of app manipulation.
 */
class API
{
    /**
     * @var Engine
     */
    protected static $moduleSystem = null;

    /**
     * API constructor.
     *
     * @param \Colibri\Application\Engine $mSystem
     */
    public function __construct(Engine &$mSystem)
    {
        self::$moduleSystem = $mSystem;
    }

    /**
     * @param string $division
     * @param string $module
     * @param string $method
     * @param array  $params
     *
     * @return string
     *
     * @throws \Colibri\Application\Exception\NotFoundException
     * @throws \LogicException
     */
    public static function callModuleMethod($division, $module, $method, ...$params)
    {
        return self::$moduleSystem->callModuleMethod($division, $module, $method, $params);
    }

    /**
     * @param string $division
     * @param string $module
     * @param string $method
     * @param array  $params
     *
     * @return string
     *
     * @throws \Colibri\Application\Exception\NotFoundException
     * @throws \LogicException
     */
    public static function getModuleView($division, $module, $method, ...$params)
    {
        return self::$moduleSystem->getModuleView($division, $module, $method, $params);
    }

    /**
     * @param array $params
     *
     * @return string
     */
    private static function getCacheKeyForCall(array $params)
    {
        $params += $_GET;
        $keyStr = '';
        foreach ($params as $param) {
            $keyStr .= serialize($param);
        }

        $keyStr .= self::$moduleSystem->domainPrefix;

        return md5($keyStr);
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @param string $division
     * @param string $module
     * @param string $method
     * @param array  $params
     *
     * @return string
     *
     * @throws \Colibri\Application\Exception\NotFoundException
     * @throws \LogicException
     */
    public static function callModuleMethodCached($division, $module, $method, ...$params)
    {
        if (Config::application('useCache') && ! DEBUG) {
            $key      = self::getCacheKeyForCall(func_get_args());
            /** @noinspection PhpUnhandledExceptionInspection */
            $retValue = Cache::remember($key, function () use ($division, $module, $method, $params) {
                /* @noinspection PhpUnhandledExceptionInspection */
                return self::callModuleMethod($division, $module, $method, ...$params);
            });
        } else {
            $retValue = self::callModuleMethod($division, $module, $method, ...$params);
        }

        return $retValue;
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @param string $division
     * @param string $module
     * @param string $method
     * @param array  $params
     *
     * @return string
     *
     * @throws \Colibri\Application\Exception\NotFoundException
     * @throws \LogicException
     */
    public static function getModuleViewCached($division, $module, $method, ...$params)
    {
        if (Config::application('useCache') && ! DEBUG) {
            $key      = self::getCacheKeyForCall(func_get_args());
            /** @noinspection PhpUnhandledExceptionInspection */
            $retValue = Cache::remember($key, function () use ($division, $module, $method, $params) {
                /* @noinspection PhpUnhandledExceptionInspection */
                return self::getModuleView($division, $module, $method, ...$params);
            });
        } else {
            $retValue = self::getModuleView($division, $module, $method, ...$params);
        }

        return $retValue;
    }

    /**
     * @param string $varName
     *
     * @return mixed
     */
    public static function getTemplateVar($varName)
    {
        return self::$moduleSystem->responser->getTemplate()->vars[$varName];
    }

    /**
     * @param string $type
     * @param array  $values
     */
    protected static function pass($type, array $values)
    {
        Session::flash($type, $values);
    }

    /**
     * Устанавливает и передаёт ошибки следующему вызванному скрипту
     * (однократно - удаляется в следующем вызванном скрипте).
     *
     * @param array $errors повторный вызов перезаписывет
     */
    public static function passErrors(array $errors)
    {
        self::pass('app_errors', $errors);
    }

    /**
     * Передаёт переменные в следующий вызванный скрипт.
     * Повторный вызов перезаписывет полностью все переменные.
     *
     * @param array $vars передаваемые переменные в виде ассоциативного массива
     */
    public static function passVars(array $vars)
    {
        self::pass('app_vars', $vars);
    }

    /**
     * @param string           $type
     * @param string|null      $key
     * @param array|mixed|null $default
     *
     * @return mixed
     */
    protected static function passed($type, $key = null, $default = null)
    {
        return Session::get(
            $type . ($key !== null ? '.' . $key : ''),
            $key === null && $default === null ? [] : $default
        );
    }

    /**
     * Возвращает переданные (из предыдущего скрипта) ошибки.
     * При вызове без параметров вернёт пустой массив.
     *
     * @param string|null      $key
     * @param array|mixed|null $default
     *
     * @return mixed
     */
    public static function errors($key = null, $default = null)
    {
        return self::passed('app_errors', $key, $default);
    }

    /**
     * Возвращает переданные (из предыдущего скрипта) переменные.
     * При вызове без параметров вернёт пустой массив.
     *
     * @param string|null      $key
     * @param array|mixed|null $default
     *
     * @return mixed
     */
    public static function vars($key = null, $default = null)
    {
        return self::passed('app_vars', $key, $default);
    }
}
