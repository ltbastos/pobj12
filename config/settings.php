<?php

return [
    'settings' => [
        'displayErrorDetails' => true,

        'db' => [
            'driver' => 'mysql',
            'host' => 'localhost:3307',
            'database' => 'POBJ',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],

        'view' => [
            'template_path' => __DIR__ . '/../src/Presentation/Views',
            'twig' => [
                'cache' => __DIR__ . '/../storage/cache/twig',
                'debug' => true,
                'auto_reload' => true,
            ],
        ],
    ]
];