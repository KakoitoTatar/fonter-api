<?php
declare(strict_types=1);

use App\Application\Middleware\JwtClaimMiddleware;
use App\Application\Middleware\SessionMiddleware;
use Slim\App;
use Slim\Middleware\ContentLengthMiddleware;

return function (App $app) {
    $app->add(JwtClaimMiddleware::class);
    $app->add(SessionMiddleware::class);
    $app->add(ContentLengthMiddleware::class);
};
