<?php
namespace Colibri\Application\Engine;


use Colibri\Base\PropertyAccess;
use Colibri\Database\Concrete\MySQL;
use Colibri\Database\Db;
use Colibri\Config\Config;
use Colibri\Database\Object;
use Colibri\Database\ObjectCollection;

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
	protected	$_db=null;
	
	public		function	__construct()
	{
		$config = Config::get('application');
		
		session_start();
		
		if (get_magic_quotes_gpc()) //  turn OFF the magic quotes !!!!!!!!!!!!!!!!!!!!
		{
			function stripslashes_deep($value)
			{
				$value = is_array($value) ?
							array_map('stripslashes_deep', $value) :
							stripslashes($value);

				return $value;
			}
			$_POST   =stripslashes_deep($_POST);
			$_GET    =stripslashes_deep($_GET);
			$_COOKIE =stripslashes_deep($_COOKIE);
			$_REQUEST=stripslashes_deep($_REQUEST);
		}
		
		if (isset($config['response']['defaultHeaders'])) {
			foreach ($config['response']['defaultHeaders'] as $header) {
				header($header);
			}
		}

		Object::$useMemcache =
		ObjectCollection::$useMemcache =
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
