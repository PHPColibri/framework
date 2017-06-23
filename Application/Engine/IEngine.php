<?php
namespace Colibri\Application\Engine;

/**
 * Engine interface
 */
interface IEngine
{
    /**
     * Engine constructor.
     */
    public function __construct();

    /**
     * @return string
     */
    public function generateResponse();
}
