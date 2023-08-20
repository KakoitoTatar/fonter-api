<?php
// application.php

require __DIR__.'/../vendor/autoload.php';

use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use \Doctrine\Migrations\Tools\Console\Command;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;

/**
 * @var $container ContainerInterface
 */
$container = require __DIR__ . '/../app/bootstrap.php';
$migrationsConfig = require __DIR__ . '/../app/migrations.php';

Dotenv\Dotenv::createImmutable(__DIR__ . '/../')->load();

if (!function_exists('env')) {
    function env($key, $default = null) {
        if ($_ENV[$key]) {
            return $_ENV[$key];
        }
        return $default;
    }
}

$configuration = new ConfigurationArray($migrationsConfig);

$dependencyFactory = DependencyFactory::fromEntityManager(
    $configuration,
    new ExistingEntityManager($container->get(\Doctrine\ORM\EntityManagerInterface::class))
);

$application = new Application();

$application->addCommands([
    new Command\DumpSchemaCommand($dependencyFactory),
    new Command\ExecuteCommand($dependencyFactory),
    new Command\GenerateCommand($dependencyFactory),
    new Command\LatestCommand($dependencyFactory),
    new Command\ListCommand($dependencyFactory),
    new Command\MigrateCommand($dependencyFactory),
    new Command\RollupCommand($dependencyFactory),
    new Command\StatusCommand($dependencyFactory),
    new Command\SyncMetadataCommand($dependencyFactory),
    new Command\VersionCommand($dependencyFactory),
    new Command\DiffCommand($dependencyFactory),
    new Command\CurrentCommand($dependencyFactory),
    new Command\UpToDateCommand($dependencyFactory),
    $container->get(\App\Infrastructure\Commands\FontsMigrator::class)
]);

$application->run();