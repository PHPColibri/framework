<?php
namespace Colibri\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Output\OutputInterface;

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

    /**
     * @param \Throwable      $exception
     * @param OutputInterface $output
     */
    public function renderError(\Throwable $exception, OutputInterface $output)
    {
        if (method_exists(get_parent_class($this), 'renderException')) {
            $this->renderException($exception, $output);
        } else {
            $this->renderThrowable($exception, $output);
        }
    }
}
