<?php
use Colibri\Database\Type as DbType;

return array(
    'connection' => array(
        //'default' => 'mysql',
        'mysql1'   => array(
            'type'       => DbType::MYSQL,
            'host'       => 'localhost',
            'database'   => 'some_db',
            'user'       => 'some_site',
            'password'   => 'some_password',
            'persistent' => false,
        ),
    ),
);
