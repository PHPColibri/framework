<?php
namespace Colibri\Application;

use Colibri\Controller;
use Colibri\Http;
use Colibri\Routing\Exception\NotFoundException;
use Colibri\Routing\Route;

class Application
{
    /** @var bool */
    private $showProfilerInfoOnDebug = true;
    /** @var bool */
    private $showAppDevToolsOnDebug = true;

    /**
     * @throws \Colibri\Http\NotFoundException
     * @throws \Colibri\Database\DbException
     */
    public function run()
    {
        $time = microtime(true);

        Application\Bootstrap::run($this);

        try {

            list($division, $module, $method, $params) = Route::resolve();

            echo $this->getModuleView($division, $module, $method, $params);

            StupidDebugger::output($time, $this->showProfilerInfoOnDebug, $this->showAppDevToolsOnDebug);

        } catch (NotFoundException $exception) {
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
     * @throws NotFoundException
     */
    public function getModuleView(string $division, string $module, string $method, array $params)
    {
        self::loadModule($division, $module);

        $className = self::getClassName($division, $module);
        if ( ! in_array($method, get_class_methods($className))) {
            throw new NotFoundException("Method '$method' does not contains in class '$className'.");
        }

        $responder = Controller\Dispatcher::call($division, $module, $className, $method, $params);

        $this->showProfilerInfoOnDebug = $responder->showProfilerInfoOnDebug;
        $this->showAppDevToolsOnDebug  = $responder->showAppDevToolsOnDebug;

        return $responder->response;
    }

    /**
     * @param string $division   name of division (as a folder name)
     * @param string $moduleName name of module (as a folder name)
     *
     * @throws NotFoundException
     */
    private static function loadModule(string $division, string $moduleName)
    {
        $mPath = $moduleName . '/' . ($division === '' ? 'primary/' : $division . '/');
        $mName = ucfirst($moduleName) . ucfirst($division);

        $fileName = MODULES . $mPath . $mName . 'ViewsController.php';

        if ( ! file_exists($fileName)) {
            throw new NotFoundException("Can't load module: file '$fileName' does not exists.");
        }

        /** @noinspection PhpIncludeInspection */
        require_once $fileName;
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
            throw new NotFoundException("Class '$className' does not exists.");
        }

        return $className;
    }
}
