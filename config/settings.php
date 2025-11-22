<?php

return [
    'settings' => [
        'displayErrorDetails' => true,

    'db' => [
        'driver' => 'mysql',
        'host' => 'srv715.hstgr.io', // ou 185.211.7.1
        'database' => 'u735745800_pobj_slim',
        'username' => 'u735745800_pobj_slim',
        'password' => 'G3XEk6:c',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
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