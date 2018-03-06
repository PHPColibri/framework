<?php
namespace Colibri\Migration\Console;

use Colibri\Console\Command as ColibriCommand;

/**
 * @method Application getApplication()
 */
abstract class Command extends ColibriCommand
{
    const COMMAND_NS = 'Colibri\Migration\Console\Command\\';

    /**
     * @param string $option
     *
     * @return array|mixed|null
     */
    public function config(string $option)
    {
        return $this->getApplication()->getConfig($option);
    }
}
