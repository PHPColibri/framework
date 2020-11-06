<?php
namespace Colibri\Migration\Console\Command;

use Colibri\Console\Command as ColibriCommand;
use Colibri\Database\Db;
use Colibri\Migration\Console\Command;

class Table extends Command
{
    /**
     * @return \Colibri\Console\Command
     */
    protected function definition(): ColibriCommand
    {
        return $this
            ->setDescription('Creates table to store migrations that was executed already.')
        ;
    }

    /**
     * @return int
     *
     * @throws \Colibri\Database\DbException
     * @throws \Colibri\Database\Exception\SqlException
     */
    protected function go(): int
    {
        /** @noinspection SqlResolve */
        Db::connection()->queries([
            'create table migrations
            (
                hash varchar(32) primary key not null,
                migratedAt timestamp default now() not null
            )',
            'alter table migrations comment = \'This table automatically created by & for Colibri Migration Tool\';',
        ]);

        $this->infoLn('Table `migrations` successfully created.');

        return 0;
    }
}
