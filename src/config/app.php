<?php

return [
    'name' => 'ATT Orders App',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => ($_ENV['APP_ENV'] ?? 'production') === 'development',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost:8080',
    'timezone' => 'Europe/Moscow',

    'pagination' => [
        'per_page' => 10,
        'max_links' => 5
    ],

    'export' => [
        'csv_delimiter' => ';',
        'csv_enclosure' => '"',
        'csv_escape' => '\\'
    ]
];