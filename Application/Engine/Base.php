<?php
namespace Colibri\Application\Engine;

use Colibri\Base\PropertyAccess;
use Colibri\Config\Config;
use Colibri\Database\AbstractDb;
use Colibri\Database\Concrete\MySQL;
use Colibri\Database\Db;
use Colibri\Database\Object;
use Colibri\Session\Session;

/**
 * Engine base class
 */
abstract class Base extends PropertyAccess implements IEngine
{
    public function __construct()
    {
        $config = Config::get('application');

        Session::start();

        if (isset($config['response']['defaultHeaders'])) {
            foreach ($config['response']['defaultHeaders'] as $header) {
                header($header);
            }
        }

        AbstractDb::$useMemcacheForMetadata =
            $config['useCache'];

        Object::$debug =
        MySQL::$monitorQueries =
            $config['debug'];

        Db::setConfig(Config::get('database'));

        $this->initialize();
    }

    abstract protected function initialize();
}
