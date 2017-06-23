<?php
namespace Colibri\Database;

use Colibri\Pattern\Helper;

class Db extends Helper
{
    /**
     * @var array
     */
    private static $config = [];
    /**
     * @var array<IDb>
     */
    private static $connection = [];

    /**
     * @param array $config
     *
     * @return array
     * @throws DbException
     */
    public static function setConfig(array $config)
    {
        if (!isset($config['connection'])) {
            throw new DbException('can`t find `connection` parameter in database config');
        }
        $connection = &$config['connection'];
        if (!isset($connection['default'])) {
            throw new DbException('can`t find `default` parameter in database config');
        }
        $default = &$connection['default'];
        if (!is_string($default) || empty($connection[$default])) {
            throw new DbException('parameter `default` must be string and contain contains name of default connection. So given section `' . $default . '` must present in database config');
        }

        return self::$config = $config;
    }

    /**
     * @param string $name connection name defined in config
     *
     * @return IDb
     * @throws DbException
     */
    public static function connection($name = 'default')
    {
        $name = $name == 'default' ? self::$config['connection']['default'] : $name;

        return isset(self::$connection[$name])
            ? self::$connection[$name]
            : self::$connection[$name] = self::createForConnection($name);
    }

    /**
     * Создает экземпляр класса используя настройки установленные ::setConfig
     *
     * @param string $name
     *
     * @return IDb Объект базы данных
     * @throws DbException
     */
    private static function createForConnection($name)
    {
        $config = &self::$config['connection'][$name];

        switch ($config['type']) {
            case Type::MYSQL:
                return new Concrete\MySQL(
                    $config['host'],
                    $config['user'],
                    $config['password'],
                    $config['database'],
                    $config['persistent']
                );
            case Type::POSTGRESQL:
            default:
                throw new DbException("can`t create database: this db type (${config['type']}) not supported");
        }
    }
}