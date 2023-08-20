<?php

// cli-config.php

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use DI\ContainerBuilder;

require __DIR__ . '/vendor/autoload.php';

$container = require 'app/bootstrap.php';

return ConsoleRunner::createHelperSet($container->get(EntityManagerInterface::class));