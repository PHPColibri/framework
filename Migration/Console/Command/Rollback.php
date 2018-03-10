<?php
namespace Colibri\Migration\Console\Command;

use Colibri\Console\Command as ColibriCommand;
use Colibri\Migration\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class Rollback extends Command
{
    use WorksWithCollection;

    protected function definition(): ColibriCommand
    {
        return $this
            ->setAliases(['down'])
            ->setDescription('Rollback migration(s)')
            ->addArgument('hash', InputArgument::OPTIONAL, 'hash of the migration that will be rollback')
            ->addOption('count', 'c', InputOption::VALUE_REQUIRED, 'rollback specified count of migrations', 1)
            ;
    }

    /**
     * @return int
     */
    protected function go(): int
    {
        $migrations = $this->migrations()
            ->onlyMigrated()
            ->withHash($this->argument('hash'))
            ->last($this->option('count'))
        ;

        $failed = false;
        foreach ($migrations as $migration) {
            $this
                ->write('Rollback migration ')
                ->info($migration->hash)
                ->write(' @ ' . $migration->createdAt)
                ->write(': ')
                ->outByWidth("<comment>$migration->name</comment>", 60)
            ;

            try {
                $migration->rollback();
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
