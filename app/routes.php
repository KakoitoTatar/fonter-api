<?php
declare(strict_types=1);

use App\Application\Actions\Font\DeleteFont;
use App\Application\Actions\Font\GetFont;
use App\Application\Actions\Font\GetFonts;
use App\Application\Actions\Font\PatchFont;
use App\Application\Actions\Font\PostFont;
use App\Application\Actions\Logotype\DeleteLogotype;
use App\Application\Actions\Logotype\GetLogotype;
use App\Application\Actions\Logotype\GetLogotypes;
use App\Application\Actions\Logotype\PatchLogotype;
use App\Application\Actions\Logotype\PostLogotype;
use App\Application\Actions\Media\DeleteMedia;
use App\Application\Actions\Media\GetMediaAsFile;
use App\Application\Actions\Media\PostMedia;
use App\Application\Actions\User\GetAuthenticatedUser;
use App\Application\Middleware\RequireAuthentification;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use App\Application\Actions\Auth\GetAuthToken;
use App\Application\Actions\User\PostUser;

return function (App $app) {
    $app->group('/api', function (Group $group) {
        $group->group('/media', function (Group $group) {
            $group->get('/download/{url:.*}', GetMediaAsFile::class);
            $group->post('', PostMedia::class)->addMiddleware(new RequireAuthentification());
//            $group->delete('/{id}', DeleteMedia::class);
        });

        $group->group('/auth', function (Group $group) {
            $group->post('', GetAuthToken::class);
            $group->get('', GetAuthenticatedUser::class)->addMiddleware(new RequireAuthentification());
        });

        $group->group('/users', function (Group $group) {
            $group->post('', PostUser::class);
        });

        $group->group('/logotypes', function (Group $group) {
            $group->get('', GetLogotypes::class);
            $group->get('/{id}', GetLogotype::class);
            $group->post('', PostLogotype::class)->addMiddleware(new RequireAuthentification());
            $group->patch('/{id}', PatchLogotype::class)->addMiddleware(new RequireAuthentification());
            $group->delete('/{id}', DeleteLogotype::class)->addMiddleware(new RequireAuthentification());
        });

        $group->group('/fonts', function (Group $group) {
            $group->get('', GetFonts::class);
            $group->get('/{id}', GetFont::class);
            $group->post('', PostFont::class)->addMiddleware(new RequireAuthentification());
            $group->patch('/{id}', PatchFont::class)->addMiddleware(new RequireAuthentification());
            $group->delete('/{id}', DeleteFont::class)->addMiddleware(new RequireAuthentification());
        });
    });

    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });
};
