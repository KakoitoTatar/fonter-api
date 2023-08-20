<?php

use DI\ContainerBuilder;

Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->load();

if (!function_exists('env')) {
    function env($key, $default = null) {
        if ($_ENV[$key]) {
            return $_ENV[$key];
        }
        return $default;
    }
}

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

if (false) { // Should be set to true in production
    $containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}

// Set up settings
$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

// Set up repositories
$repositories = require __DIR__ . '/../app/repositories.php';
$repositories($containerBuilder);

$customValidators = require  __DIR__ . '/../app/validators.php';
$customValidators($containerBuilder);

// Build PHP-DI Container instance
return $containerBuilder->build();
