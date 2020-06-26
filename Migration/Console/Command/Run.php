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
            ->setAliases(['up', 'migrate'])
            ->setDescription('Run migration(s)')
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

        $failed = $this->catchErrors(function () use ($migrations) {
            $this->runMigrations($migrations);
        });

        return $failed ? 1 : 0;
    }

    /**
     * @param \Colibri\Migration\Migration\Collection $migrations
     *
     * @throws \Throwable
     */
    protected function runMigrations(\Colibri\Migration\Migration\Collection $migrations)
    {
        foreach ($migrations as $migration) {
            $this
                ->write('Running migration ')
                ->info($migration->hash)
                ->write(' @ ' . $migration->createdAt)
                ->write(': ')
                ->outByWidth("<comment>$migration->name</comment>", 60)
                ->okOrFail(function () use ($migration) {
                    $migration->run();
                })
            ;
        }
    }
}
