<?php
namespace Colibri\Application\Engine;

use Colibri\Base\PropertyAccess;
use Colibri\Cache\Cache;
use Colibri\Config\Config;
use Colibri\Database\AbstractDb;
use Colibri\Database\Concrete\MySQL;
use Colibri\Database\Db;
use Colibri\Session\Session;

/**
 * Engine base class.
 */
abstract class Base extends PropertyAccess implements EngineInterface
{
    /**
     * Base constructor.
     *
     * @throws \Colibri\Database\DbException
     * @throws \InvalidArgumentException
     */
    public function __construct()
    {
        $config = Config::get('application');

        Session::start();

        if (isset($config['response']['defaultHeaders'])) {
            foreach ($config['response']['defaultHeaders'] as $header) {
                header($header);
            }
        }

        AbstractDb::$useCacheForMetadata =
            $config['useCache'];

        MySQL::$monitorQueries = $config['debug'];

        Db::setConfig(Config::get('database'));
        Cache::setConfig(Config::getOrEmpty('cache'));

        $this->initialize();
    }

    /**
     * @return void
     */
    abstract protected function initialize();
}
