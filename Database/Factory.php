<?php
namespace Colibri\Database;

/**
 * Database\Factory, Абстрактная фабрика для класса базы даных
 *  
 * @author		alek13
 * @version		1.0.1
 * @package		Colibri
 * @subpackage	a13FW
 *
 * @exception	6xx
 */
final class Factory
{
    /**
     * @var array
     */
    protected $config = [];
    /**
     * Создает экземпляр класса используя настройки Config::get('database')
     *
     * @param array $connectionConfig pass null to load from `database.php` by
     *
     * @return IDb Объект базы данных или генерит ошибку
     * @throws DbException
     */
	static public function createDb(array $connectionConfig = null)
	{
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $config = $connectionConfig
            ? $connectionConfig
            : \Colibri\Config\Config::database('connection');
		if (!isset($config['default']))
			throw new DbException('can`t find `default` section in database config');

        $default = $config['default'];
		$config = is_array($default)
			? $default
			: $config[$default]
		;
		
		switch ($config['type']) {
			case Type::MYSQL:
				return new MySQL(
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
