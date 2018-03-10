<?php
namespace Colibri\Migration\Console\Command;

use Colibri\Console\Command as ColibriCommand;
use Colibri\Migration\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Run extends Command
{
    use WorksWithCollection;

    protected function definition(): ColibriCommand
    {
        return $this
            ->setDescription('Run migration(s).')
            ->addArgument('hash', InputArgument::OPTIONAL, 'hash of the migration that will be run')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'run all migrations')
            ;
    }

    /**
     * @return int
     */
    protected function go(): int
    {
        $migrations = $this->migrations()
            ->thatNotMigrated()
            ->one( ! $this->option('all'))
            ->withHash($this->argument('hash'))
        ;

        $failed = false;
        foreach ($migrations as $migration) {
            $this
                ->write('Running migration ')
                ->info($migration->hash)
                ->write(' @ ' . $migration->createdAt)
                ->write(': ')
                ->outByWidth("<comment>$migration->name</comment>", 60)
            ;

            try {
                $migration->run();
                $this->ok();
            } catch (\Throwable $exception) {
                $this->fail();
                $this->getApplication()->renderException($exception, $this->output);
                $failed = true;
            }
        }

        return $failed ? 1 : 0;
    }
}
