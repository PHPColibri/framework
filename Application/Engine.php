<?php
namespace Colibri\Application;

use Colibri\Base\PropertyAccess;
use Colibri\Cache\Cache;
use Colibri\Config\Config;
use Colibri\Controller\MethodsController;
use Colibri\Controller\ViewsController;
use Colibri\Database\AbstractDb\Driver;
use Colibri\Database\Db;
use Colibri\Http;
use Colibri\Log\Log;
use Colibri\Session\Session;
use Colibri\Util\Arr;
use Colibri\Util\Str;
use LogicException;

/**
 * Description of CModuleEngine.
 *
 * @property string          $domainPrefix
 * @property ViewsController $responser
 * @property bool            $showProfilerInfoOnDebug
 * @property bool            $showAppDevToolsOnDebug
 */
class Engine extends PropertyAccess
{
    /**
     * @var ViewsController|MethodsController
     */
    protected $_responser = null;
    /**
     * @var string
     */
    protected $_domainPrefix = null;
    /**
     * @var string
     */
    private $_division = null;
    /**
     * @var string
     */
    private $_module = null;
    /**
     * @var string
     */
    private $_method = null;
    /**
     * @var array
     */
    private $_params = [];

    /**
     * @var bool
     */
    protected $_showProfilerInfoOnDebug = true;
    /**
     * @var bool
     */
    protected $_showAppDevToolsOnDebug = true;

    /**
     * Base constructor.
     *
     * @throws \Colibri\Database\DbException
     * @throws \InvalidArgumentException
     */
    public function __construct()
    {
        $config = Config::get('application');

        $this->configure($config);

        Session::start();

        if (isset($config['response']['defaultHeaders'])) {
            foreach ($config['response']['defaultHeaders'] as $header) {
                header($header);
            }
        }

        $this->initialize();
    }

    /**
     * @return void
     */
    private static function setUpErrorHandling()
    {
        error_reporting(-1);
        ini_set('display_errors', DEBUG);
        Error\Handler::register(DEBUG);
    }

    /**
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function initialize()
    {
        $appConfig = $this->bootstrap();

        $this->_domainPrefix = $this->getDomainPrefix();

        $this->initAPI($appConfig);


        $requestedUri = $this->getRequestedUri();
        /** @noinspection PhpUndefinedMethodInspection */
        $routes = Config::routing('rewrite');
        foreach ($routes as $route) {
            $pattern      = $route['pattern'];
            $replacement  = $route['replacement'];
            $requestedUri = preg_replace($pattern, $replacement, $requestedUri);

            if (isset($route['last'])) {
                break;
            }
        }

        $this->parseRequestedFile($requestedUri);
    }

    /**
     * @return string returns requested file name with path: for
     *                "http://example.com/some/dir/somefile.php?arg1=val1&arg2=val2" returns
     *                "/some/dir/somefile.php"
     */
    private function getRequestedUri()
    {
        $questPos = strpos($_SERVER['REQUEST_URI'], '?');
        if ($questPos === false) {
            return $_SERVER['REQUEST_URI'];
        }

        return substr($_SERVER['REQUEST_URI'], 0, $questPos);
    }

    /**
     * @return string returns prefix of domain: for "sub.domain.example.com" and const $conf['domain']=="example.com",
     *                returns "sub.domain"
     *
     * @throws \InvalidArgumentException
     */
    private function getDomainPrefix()
    {
        $appConfig = Config::get('application');
        $prefix    = str_replace($appConfig['domain'], '', $_SERVER['HTTP_HOST']);
        $pLen      = strlen($prefix);
        if ($pLen) {
            $prefix = substr($prefix, 0, $pLen - 1);
        }

        return $prefix;
    }

    /**
     * @param string $file requested file name
     *
     * @throws \InvalidArgumentException
     */
    protected function parseRequestedFile($file)
    {
        $file = ltrim($file, '/');

        $moduleConfig = Config::application('module');

        $parts    = explode('/', $file);
        $partsCnt = count($parts);

        if ($partsCnt > 0 && in_array($parts[0], Config::get('divisions'))) {
            $this->_division = $parts[0];
            $parts           = array_slice($parts, 1);
        } else {
            $this->_division = '';
        }

        $this->_module = empty($parts[0])
            ? $moduleConfig['default']
            : $parts[0];

        $this->_method = $partsCnt < 2 || empty($parts[1])
            ? $moduleConfig['defaultViewsControllerAction']
            : Str::camel($parts[1]);

        if ($partsCnt > 2) {
            $this->_params = array_slice($parts, 2);
        }
    }

    /**
     * @return string
     *
     * @throws Http\NotFoundException
     * @throws LogicException
     */
    public function generateResponse()
    {
        try {
            return $this->getModuleView($this->_division, $this->_module, $this->_method, $this->_params);
        } catch (Exception\NotFoundException $exception) {
            throw new Http\NotFoundException($exception->getMessage(), $exception->getCode(), $exception);
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
     * @throws Exception\NotFoundException
     * @throws LogicException
     */
    public function getModuleView($division, $module, $method, $params)
    {
        $this->loadModule($division, $module);

        $className = self::getClassName($division, $module);
        /** @var ViewsController|MethodsController $responder */
        $responder        = new $className($division, $module, $method);
        $this->_responser = &$responder;

        if ( ! in_array($method, get_class_methods($className))) {
            throw new Exception\NotFoundException("Method '$method' does not contains in class '$className'.");
        }

        call_user_func_array([&$responder, 'setUp'], $params);
        call_user_func_array([&$responder, $method], $params);
        call_user_func_array([&$responder, 'tearDown'], $params);

        $this->_showProfilerInfoOnDebug = $responder->showProfilerInfoOnDebug;
        $this->_showAppDevToolsOnDebug  = $responder->showAppDevToolsOnDebug;

        return $responder->response;
    }

    /**
     * @param string $division   name of division (as a folder name)
     * @param string $moduleName name of module (as a folder name)
     *
     * @throws Exception\NotFoundException
     * @throws LogicException
     */
    private function loadModule($division, $moduleName)
    {
        $mPath = $moduleName . '/' . ($division === '' ? 'primary/' : $division . '/');
        $mName = ucfirst($moduleName) . ucfirst($division);

        $fileName = MODULES . $mPath . $mName . 'ViewsController.php';

        if ( ! file_exists($fileName)) {
            throw new Exception\NotFoundException("Can't load module: file '$fileName' does not exists.");
        } else {
            /** @noinspection PhpIncludeInspection */
            require_once $fileName;
        }
    }

    /**
     * @param string $division
     * @param string $module
     *
     * @return string
     */
    private static function getClassName(string $division, string $module): string
    {
        $className =
            ucfirst($module) .
            ucfirst($division) .
            'ViewsController';

        if ( ! class_exists($className)) {
            throw new Exception\NotFoundException("Class '$className' does not exists.");
        }

        return $className;
    }

    /**
     * @return array
     */
    protected function bootstrap(): array
    {
        $appConfig = Config::get('application');
        define('DEBUG', (bool)Arr::get($appConfig, 'debug', false));
        self::setUpErrorHandling();
        mb_internal_encoding($appConfig['encoding']);
        date_default_timezone_set($appConfig['timezone']);
        setlocale(LC_TIME, $appConfig['locale']);
        umask($appConfig['umask']);

        return $appConfig;
    }

    /**
     * @param $config
     *
     * @throws \Colibri\Database\DbException
     */
    private function configure($config)
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
    protected function initAPI(array $appConfig)
    {
        $apiClass = $appConfig['API'] ?? API::class;
        $api      = new $apiClass($this);
        if ( ! $api instanceof API) {
            throw new \DomainException('Application config `API` param must referenced to `' . API::class . '` extension');
        }
    }
}
