<?php
namespace Colibri\Controller;

use Colibri\Pattern\Helper;

class Dispatcher extends Helper
{
    /**
     * @param string $division
     * @param string $module
     * @param string $class
     * @param string $method
     * @param array  $params
     *
     * @return \Colibri\Controller\ViewsController
     */
    public static function call(string $division, string $module, string $class, string $method, array $params)
    {
        /** @var ViewsController $responder */
        $responder = new $class($division, $module, $method);

        call_user_func_array([&$responder, 'setUp'], $params);
        call_user_func_array([&$responder, $method], $params);
        call_user_func_array([&$responder, 'tearDown'], $params);

        return $responder;
    }
}
