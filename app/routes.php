<?php

declare(strict_types=1);

use App\Application\Actions\Group\GroupAction;
use App\Application\Actions\Member\CreateBirthdayAction;
use App\Application\Actions\Member\ListBirthdaysAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as RouteGroup;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->group('/webhooks/congressus', function (RouteGroup $routeGroup) {
        $routeGroup->group('/member', function (RouteGroup $memberRouteGroup) {
            $memberRouteGroup->get('/todays-birthdays', ListBirthdaysAction::class)->add("BasicAuth.BirthdayConsumer");
            $memberRouteGroup->post('/birthday', CreateBirthdayAction::class)->add("BasicAuth.BirthdayProducer");
        });

        $routeGroup->post('/group', GroupAction::class);
    });
};
