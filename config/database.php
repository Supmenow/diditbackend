<?php

    return[
        'fetch' => PDO::FETCH_CLASS,
        
        'default' => env('DB_CONNECTION', 'didit'),

        'connections' => [

            'didit' => [
                'driver'    => 'mysql',
                'host'      => env('DB_HOST', 'localhost'),
                'database'  => env('DB_DATABASE', 'didit'),
                'username'  => env('DB_USERNAME', 'homestead'),
                'password'  => env('DB_PASSWORD', ''),
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
                'strict'    => false
            ],
        ],

        'migrations' => 'migrations',
    ];
