<?php
namespace Colibri\Application\Engine;


use Colibri\Base\PropertyAccess;
use Colibri\Database\AbstractDb;
use Colibri\Database\Concrete\MySQL;
use Colibri\Database\Db;
use Colibri\Config\Config;
use Colibri\Database\Object;
use Colibri\Session\Session;

/**
 * Description of CEngine
 *
 * @author		Александр Чибрикин aka alek13 <alek13.me@gmail.com>
 * @package		xTeam
 * @subpackage	a13FW
 * @version		1.01.1
 * @exception	10xx
 */
abstract
class Base extends PropertyAccess implements IEngine
{
	public		function	__construct()
	{
		$config = Config::get('application');
		
		Session::start();
		
		if (isset($config['response']['defaultHeaders'])) {
			foreach ($config['response']['defaultHeaders'] as $header) {
				header($header);
			}
		}

		AbstractDb::$useMemcacheForMetadata =
			$config['useCache']
		;

		Object::$debug =
		MySQL::$monitorQueries =
			$config['debug']
		;

        Db::setConfig(Config::get('database'));

		$this->initialize();
	}
	abstract
	protected		function	initialize();

}
