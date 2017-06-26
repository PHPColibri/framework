<?php
namespace Colibri\Application;

use Colibri\Config\Config;
use Colibri\Controller\MethodsController;
use Colibri\Controller\ViewsController;
use Colibri\Http;
use Colibri\Log\Log;
use Colibri\Util\Str;
use LogicException;

/**
 * Description of CModuleEngine.
 *
 * @property string                            $domainPrefix
 * @property MethodsController|ViewsController $responser
 * @property bool                              $showProfilerInfoOnDebug
 * @property bool                              $showAppDevToolsOnDebug
 */
class Engine extends Engine\Base
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
     * @return void
     */
    private static function setUpErrorHandling()
    {
        error_reporting(0xffff);
        ini_set('display_errors', DEBUG);
        set_error_handler('\Colibri\Application\Engine::errorHandler', 0xffff);
        set_exception_handler('\Colibri\Application\Engine::exceptionHandler');
    }

    /**
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function initialize()
    {
        $appConfig = Config::get('application');
        mb_internal_encoding($appConfig['encoding']);
        date_default_timezone_set($appConfig['timezone']);
        setlocale(LC_TIME, $appConfig['locale']);
        umask($appConfig['umask']);
        define('DEBUG', $appConfig['debug']);
        self::setUpErrorHandling();

        $this->_domainPrefix = $this->getDomainPrefix();

        new \API($this); // initialize API

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
        $appConfig = Config::get('application');

        $dotPos = strpos($file, '.');
        if ($dotPos !== false) {
            $file = substr($file, 0, $dotPos);
        }
        if ($file[0] === '/') {
            $file = substr($file, 1);
        }

        $parts    = explode('/', $file);
        $partsCnt = count($parts);

        if ($partsCnt > 0 && in_array($parts[0], Config::get('divisions'))) {
            $this->_division = $parts[0];
            $parts           = array_slice($parts, 1);
        } else {
            $this->_division = '';
        }

        if (empty($parts[0])) {
            $this->_module = $appConfig['module']['default'];
        } else {
            $this->_module = $parts[0];
        }
        if ($partsCnt < 2 ||
            empty($parts[1])) {
            $this->_method = $appConfig['module']['defaultViewsControllerAction'];
        } else {
            $this->_method = Str::camel($parts[1]);
        }

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
        return $this->callModuleEssence(CallType::view, $division, $module, $method, $params);
    }

    /**
     * @param $division
     * @param $module
     * @param $method
     * @param $params
     *
     * @return string
     *
     * @throws \Colibri\Application\Exception\NotFoundException
     * @throws \LogicException
     */
    public function callModuleMethod($division, $module, $method, $params)
    {
        return $this->callModuleEssence(CallType::method, $division, $module, $method, $params);
    }

    /**
     * @param $type
     * @param $division
     * @param $module
     * @param $method
     * @param $params
     *
     * @return string
     *
     * @throws \Colibri\Application\Exception\NotFoundException
     * @throws \LogicException
     */
    private function callModuleEssence($type, $division, $module, $method, $params)
    {
        $this->loadModule($division, $module, $type);

        $className = ucfirst($module) . ucfirst($division) . ($type == CallType::view ? 'Views' : 'Methods') . 'Controller';
        if ( ! class_exists($className)) {
            throw new Exception\NotFoundException("Class '$className' does not exists.");
        }
        /** @var ViewsController|MethodsController $responser */
        $responser        = new $className($division, $module, $method);
        $this->_responser = &$responser;

        $classMethods = get_class_methods($className);
        if ( ! in_array($method, $classMethods)) {
            throw new Exception\NotFoundException("Method '$method' does not contains in class '$className'.");
        }

        call_user_func_array([&$responser, 'setUp'], $params);
        $response = call_user_func_array([&$responser, $method], $params);
        call_user_func_array([&$responser, 'tearDown'], $params);

        if ($type == CallType::view) {
            $this->_showProfilerInfoOnDebug = $responser->showProfilerInfoOnDebug;
            $this->_showAppDevToolsOnDebug  = $responser->showAppDevToolsOnDebug;
        }

        if ($type == CallType::view) {
            return $responser->response;
        }

        return $response;
    }

    /**
     * @param string $division   name of division (as a folder name)
     * @param string $moduleName name of module (as a folder name)
     * @param int    $type       one of CallType::<const> 'views' or 'methods'
     *
     * @throws Exception\NotFoundException
     * @throws LogicException
     */
    private function loadModule($division, $moduleName, $type = CallType::view)
    {
        $mPath = $moduleName . '/' . ($division === '' ? 'primary/' : $division . '/');
        $mName = ucfirst($moduleName) . ucfirst($division);

        $fileName = MODULES . $mPath;
        if ($type == CallType::view) {
            $fileName .= $mName . 'ViewsController.php';
        } elseif ($type == CallType::method) {
            $fileName .= $mName . 'Methods.php';
        } else {
            throw new LogicException("Unknown CallType $type");
        }

        if ( ! file_exists($fileName)) {
            throw new Exception\NotFoundException("Can't load module: file '$fileName' does not exists.");
        } else {
            // @todo remove this (carefully) /** @noinspection PhpIncludeInspection */
            require_once $fileName;
        }
    }

    /**
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     *
     * @throws \Exception
     */
    public static function errorHandler($code, $message, $file, $line)
    {
        throw new \Exception("php error [$code]: '$message' in $file:$line");
    }

    /**
     * @param \Throwable|\Exception $exc
     *
     * @throws \InvalidArgumentException
     */
    public static function exceptionHandler($exc)
    {
        $message = $exc->__toString();
        if (DEBUG) {
            /* @noinspection PhpUnusedLocalVariableInspection variable uses in 500.php */
            $error = $message;
        }

        include HTTPERRORS . '500.php';

        Log::add($message, 'core.module');
    }
}
