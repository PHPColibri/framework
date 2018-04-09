<?php

use Colibri\Application\Application;
use Colibri\Application\Application\Error\Handler;
use Colibri\Http\NotFoundException;
use Colibri\Log\Log;

try {

    $application = new Application();
    /** @noinspection PhpUnhandledExceptionInspection */
    $application->run();

} catch (NotFoundException $exc) {
    if (DEBUG) {
        /** @noinspection PhpUnhandledExceptionInspection */
        throw $exc;
    }

    Log::notFound()->warning('Not Found', [
        'uri'     => $_SERVER['REQUEST_URI'],
        'referer' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''),
    ]);

    Handler::showError($exc, 404, 'Not Found');
}
