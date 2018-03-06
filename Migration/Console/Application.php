<?php
namespace Colibri\Migration\Console;

use Colibri\Console\Application as ColibriApplication;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Command\ListCommand;

class Application extends ColibriApplication
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * Application constructor.
     *
     * @param string $name
     * @param string $version
     */
    public function __construct(string $name = 'UNKNOWN', string $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
        $this->setDefaultCommand('commands');
    }

    /**
     * @param string $option
     *
     * @return array|mixed|null
     */
    public function getConfig(string $option = null)
    {
        return $option !== null
            ? ($this->config[$option] ?? null)
            : $this->config;
    }

    /**
     * @param array $config
     *
     * @return \Colibri\Migration\Console\Application
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return array|\Symfony\Component\Console\Command\Command[]
     */
    protected function getDefaultCommands()
    {
        return [
            new HelpCommand(),
            (new ListCommand())->setName('commands'),
        ];
    }
}
