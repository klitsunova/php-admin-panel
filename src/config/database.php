<?php
return [
    'host' => $_ENV['DB_HOST'] ?? 'db',
    'database' => $_ENV['DB_NAME'] ?? 'att_test_db',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? 'root',
];
