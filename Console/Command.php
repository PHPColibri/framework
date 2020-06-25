<?php
namespace Colibri\Console;

use Colibri\Util\Str;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class Command extends SymfonyCommand
{
    const COMMAND_NS = 'Application\Command\\';

    /** @var InputInterface */
    protected $input;
    /** @var OutputInterface */
    protected $output;
    /** @var \Symfony\Component\Console\Style\SymfonyStyle */
    protected $io;

    /**
     * @return Command
     */
    protected function configure()
    {
        return $this
            ->setName($this->detectName())
            ->definition()
            ;
    }

    /**
     * Auto-detect command name by class name & namespace.
     *
     * @return string
     */
    protected function detectName()
    {
        $name = Str::cut(static::class, static::COMMAND_NS);
        $name = Str::snake($name, '-');
        $name = str_replace('\\-', ':', $name);

        return $name;
    }

    abstract protected function definition(): self;

    /**
     * Executes the current command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input  = $input;
        $this->output = $output;
        $this->io     = new SymfonyStyle($this->input, $this->output);

        return $this->go();
    }

    /**
     * Implement this method with command execution logic.
     *
     * @return int Application exit code.
     */
    abstract protected function go(): int;

    /**
     * @param string $string
     *
     * @return $this
     */
    protected function write(string $string)
    {
        $this->output->write($string);

        return $this;
    }

    /**
     * @param string $string
     *
     * @return $this
     */
    protected function writeLn(string $string)
    {
        return $this->write($string)->ln();
    }

    /**
     * @param string $string
     *
     * @return $this
     */
    protected function info(string $string)
    {
        $this->output->write("<info>$string</info>");

        return $this;
    }

    /**
     * @param string $string
     *
     * @return $this
     */
    protected function infoLn(string $string)
    {
        return $this->info($string)->ln();
    }

    /**
     * @param string $string
     *
     * @return $this
     */
    protected function comment(string $string)
    {
        $this->output->write("<comment>$string</comment>");

        return $this;
    }

    /**
     * @param string $string
     *
     * @return $this
     */
    protected function error(string $string)
    {
        $this->io->error($string);

        return $this;
    }

    /**
     * @param string $string
     *
     * @return $this
     */
    protected function commentLn(string $string)
    {
        return $this->comment($string)->ln();
    }

    /**
     * @param string $string
     *
     * @return $this
     */
    protected function bold(string $string)
    {
        $this->output->write($string);

        return $this;
    }

    /**
     * @param string $string
     *
     * @return $this
     */
    protected function boldLn(string $string)
    {
        $this->bold($string);
        $this->output->writeln('');

        return $this;
    }

    /**
     * Returns the option value for a given option name.
     *
     * @param string $name The option name
     *
     * @return mixed The option value
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException When option given doesn't exist
     */
    protected function option(string $name)
    {
        return $this->input->getOption($name);
    }

    /**
     * Returns the argument value for a given argument name.
     *
     * @param string $name The argument name
     *
     * @return mixed The argument value
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException When argument given doesn't exist
     */
    protected function argument(string $name)
    {
        return $this->input->getArgument($name);
    }

    /**
     * @param string $string
     * @param int    $width
     * @param string $suffix
     *
     * @return $this
     */
    protected function outByWidth(string $string, int $width = 160, string $suffix = '...')
    {
        /** @var \Symfony\Component\Console\Helper\FormatterHelper $formatter */
        $formatter = $this->getHelper('formatter');

        $suffixLen = $formatter->strlen($suffix);
        $string    = $formatter->truncate($string, $width - $suffixLen, $suffix);
        $length    = $formatter->strlen($string);

        $this->output->write($string . str_repeat(' ', $width - $length));

        return $this;
    }

    /**
     * Just outputs green `[ OK ]\n`.
     *
     * @return $this
     */
    protected function ok()
    {
        $this->output->writeln('[ <info>OK</info> ]');

        return $this;
    }

    /**
     * @return $this
     */
    protected function ✔()
    {
        return $this->info('✔');
    }

    /**
     * @return $this
     */
    protected function ✖()
    {
        return $this->error('✖');
    }

    /**
     * Just outputs yellow `[SKIP]\n`.
     *
     * @return $this
     */
    protected function skip()
    {
        $this->output->writeln('[<comment>SKIP</comment>]');

        return $this;
    }

    /**
     * Just outputs red `[FAIL]\n`.
     *
     * @return $this
     */
    protected function fail()
    {
        $this->output->writeln('[<error>FAIL</error>]');

        return $this;
    }

    /**
     * @param callable $callback
     *
     * @return $this
     *
     * @throws \Throwable
     */
    protected function okOrFail(callable $callback)
    {
        try {
            $callback();
            $this->ok();
        } catch (\Throwable $exception) {
            $this->fail();
            throw $exception;
        }

        return $this;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return int
     *
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     * @throws \Exception
     */
    protected function call(string $name, array $arguments = []): int
    {
        return $this->getApplication()
            ->find($name)
            ->run(new ArrayInput($arguments), $this->output)
            ;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    protected function header(string $title)
    {
        $this->io->title("<fg=cyan>$title</>");

        return $this;
    }

    /**
     * @return $this
     */
    protected function ln()
    {
        $this->output->writeln('');

        return $this;
    }

    /**
     * @param array $headers
     * @param array $rows
     *
     * @return \Colibri\Console\Command
     */
    protected function table(array $headers, array $rows)
    {
        (new Table($this->output))
            ->setHeaders($headers)
            ->setRows($rows)
            ->render()
        ;

        return $this;
    }

    /**
     * @param string $question
     * @param bool   $default
     *
     * @return bool
     *
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     */
    protected function confirm(string $question, bool $default = false): bool
    {
        return $this->io->confirm($question, $default);
    }
}
