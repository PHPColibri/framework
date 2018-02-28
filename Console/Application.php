<?php
namespace Colibri\Console;

use Symfony\Component\Console\Application as SymfonyApplication;

class Application extends SymfonyApplication
{
    /** @var string App Logo */
    private $logo = '';

    /**
     * @param string $logo
     *
     * @return $this
     */
    public function setLogo(string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * @param array $commands
     *
     * @return $this
     */
    public function addCommands(array $commands): self
    {
        parent::addCommands($commands);

        return $this;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return $this->logo . PHP_EOL . parent::getHelp();
    }
}
