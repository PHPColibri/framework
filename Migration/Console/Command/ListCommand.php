<?php
namespace Colibri\Migration\Console\Command;

use Colibri\Console\Command as ColibriCommand;
use Colibri\Migration\Console\Command;

class ListCommand extends Command
{
    use WorksWithCollection;

    /**
     * @return \Colibri\Console\Command
     */
    protected function definition(): ColibriCommand
    {
        return $this
            ->setName('list')
            ->setDescription('List all migrations')
            ;
    }

    /**
     * @return int
     */
    protected function go(): int
    {
        $rows = [];
        foreach ($this->migrations() as $migration) {
            $rows [] = [
                $migration->migrated() ? "<info>$migration->hash</info>" : "<comment>$migration->hash</comment>",
                $migration->name,
                $migration->createdAt,
                $migration->migrated()
                    ? "<info>✔</info> $migration->migratedAt"
                    : '<fg=red>✖</>',
            ];
        }
        $this->table(['hash', 'name', 'created at', 'migrated at'], $rows);

        return 0;
    }
}
