<?php

use Colibri\Database\Type as DbType;

return [
    'connection' => [
        //'default' => 'mysql',
        'mysql1' => [
            'type'       => DbType::MYSQL,
            'host'       => 'localhost',
            'database'   => 'some_db',
            'user'       => 'some_site',
            'password'   => 'some_password',
            'persistent' => false,
        ],
    ],
];
