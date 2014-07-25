<?php
namespace Colibri\Database;

use Colibri\Config\Config;

/**
 * Database\Factory, Абстрактная фабрика для класса базы даных
 *  
 * @author		Антон Марченко, a13
 * @version		1.0.1
 * @package		xTeam
 * @subpackage	a13FW
 *
 * @exception	6xx
 */
final class Factory
{

	/**
	 * Создает экземпляр класса используя настройки Config::get('database')
	 *
	 * @return IDb Объект базы данных или генерит ошибку
	 */
	static public function createDb()
	{
		$config = Config::get('database');
		$config = $config['connection'];
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
				throw new \Exception("can`t create database: this db type ($db_type) not supported");
		}
	}

}
