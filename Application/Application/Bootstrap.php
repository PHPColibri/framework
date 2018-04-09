<?php
namespace Colibri\Application\Application;

use Colibri\Application\Application;
use Colibri\Cache\Cache;
use Colibri\Config\Config;
use Colibri\Database\AbstractDb\Driver;
use Colibri\Database\Db;
use Colibri\Log\Log;
use Colibri\Pattern\Helper;
use Colibri\Session\Session;
use Colibri\Util\Arr;

class Bootstrap extends Helper
{
    /**
     * @param \Colibri\Application\Application $app
     *
     * @throws \Colibri\Database\DbException
     */
    public static function run(Application $app)
    {
        $config = Config::getOrEmpty('application');
        $debug  = (bool)Arr::get($config, 'debug', false);
        define('DEBUG', $debug);

        self::setUpErrorHandling($debug);
        self::configure($config);
        self::bootstrap($config);
        self::initAPI($config, $app);

        Session::start();

        foreach (Arr::get($config, 'response.defaultHeaders', []) as $header) {
            header($header);
        }
    }

    /**
     * @param $config
     *
     * @throws \Colibri\Database\DbException
     */
    private static function configure($config)
    {
        Driver\Connection\Metadata::$useCacheForMetadata = $config['useCache'];
        Driver\Connection::$monitorQueries               = $config['debug'];

        Db::setConfig(Config::get('database'));
        Cache::setConfig(Config::getOrEmpty('cache'));
        Log::setConfig(Config::getOrEmpty('log'));
    }

    /**
     * @param array $appConfig
     */
    private static function bootstrap(array $appConfig)
    {
        mb_internal_encoding($appConfig['encoding']);
        date_default_timezone_set($appConfig['timezone']);
        setlocale(LC_TIME, $appConfig['locale']);
        umask($appConfig['umask']);
    }

    /**
     * @param bool $debug
     */
    private static function setUpErrorHandling($debug = false)
    {
        error_reporting(-1);
        ini_set('display_errors', $debug);
        Error\Handler::register($debug);
    }

    /**
     * @param array $appConfig
     * @param       $app
     */
    private static function initAPI(array $appConfig, $app)
    {
        $apiClass = $appConfig['API'] ?? API::class;
        $api      = new $apiClass($app);
        if ( ! $api instanceof API) {
            throw new \DomainException('Application config `API` param must referenced to `' . API::class . '` extension');
        }
    }
}
