<?php
namespace Colibri\Application\Engine;

/**
 * Engine interface
 */
interface IEngine
{
    public function __construct();

    public function generateResponse();
}
