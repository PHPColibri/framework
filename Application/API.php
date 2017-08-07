<?php
namespace Colibri\Application;

use Colibri\Cache\Memcache;
use Colibri\Config\Config;

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
     * @var array assoc-array of errors (key/field => error-message);
     */
    public static $errors = null;
    /**
     * @var array assoc-array of vars (name => value);
     */
    public static $vars = null;

    /**
     * API constructor.
     *
     * @param \Colibri\Application\Engine $mSystem
     */
    public function __construct(Engine &$mSystem)
    {
        self::$moduleSystem = $mSystem;

        static::init();
    }

    /**
     * @return void
     */
    protected static function init()
    {
        static::initFromSession();
    }

    /**
     * @return void
     */
    protected static function initFromSession()
    {
        if (isset($_SESSION['api_errors'])) {
            self::$errors = unserialize($_SESSION['api_errors']);
            unset($_SESSION['api_errors']);
        }
        if (isset($_SESSION['api_vars'])) {
            self::$vars = unserialize($_SESSION['api_vars']);
            unset($_SESSION['api_vars']);
        }
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

    /**
     * @param string $division
     * @param string $module
     * @param string $method
     * @param array  $params
     *
     * @return string
     *
     * @throws \Colibri\Application\Exception\NotFoundException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public static function callModuleMethodCached($division, $module, $method, ...$params)
    {
        if (Config::application('useCache') && ! DEBUG) {
            $key      = self::getCacheKeyForCall(func_get_args());
            $retValue = Memcache::remember($key, function () use ($division, $module, $method, $params) {
                return self::callModuleMethod($division, $module, $method, ...$params);
            });
        } else {
            $retValue = self::callModuleMethod($division, $module, $method, ...$params);
        }

        return $retValue;
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
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public static function getModuleViewCached($division, $module, $method, ...$params)
    {
        if (Config::application('useCache') && ! DEBUG) {
            $key      = self::getCacheKeyForCall(func_get_args());
            $retValue = Memcache::remember($key, function () use ($division, $module, $method, $params) {
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
     * @param string     $sessionKey
     * @param array|null $values
     */
    protected static function pass($sessionKey, array $values = null)
    {
        if ($values === null) {
            unset($_SESSION[$sessionKey]);

            return;
        }

        $existingErrors        = isset($_SESSION[$sessionKey])
            ? unserialize($_SESSION[$sessionKey])
            : [];
        $_SESSION[$sessionKey] = serialize(array_merge($existingErrors, $values));
    }

    /**
     * передаёт устанавливает и передаёт ошибки следующему вызванному скрипту-странице (однократно - удаляется в
     * след.вызв-ом скрипте само).
     *
     * @param array|null $errors повторный вызов добавляет или перезаписывет с существующими ключами.
     *                           вызов со значением null стирает ошибки и отменяет передачу в след. контроллер
     */
    public static function passErrors(array $errors = null)
    {
        self::pass('api_errors', $errors);
    }

    /**
     * передаёт переменные в следующий вызванный скрипт
     *
     * @param array|null $vars передаваемые переменные в виде ассоциативного массива.
     *                         повторный вызов добавляет или перезаписывет с существующими ключами.
     *                         вызов со значением null стирает ошибки и отменяет передачу в след. контроллер
     */
    public static function passVars(array $vars = null)
    {
        self::pass('api_vars', $vars);
    }

    /**
     * @param string $session_id
     */
    public static function catchSession($session_id)
    {
        // @todo move this into Session
        session_write_close();
        session_id($session_id);
        session_start();
        self::initFromSession();
    }
}
