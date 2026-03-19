<?php

declare(strict_types=1);

use App\Application\Actions\Member\CreateBirthdayAction;
use App\Application\Actions\Member\ListBirthdaysAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->group('/webhooks/congressus', function (Group $group) {
        $group->group('/member', function (Group $group) {
            $group->get('/todays-birthdays', ListBirthdaysAction::class)->add("BasicAuth.BirthdayConsumer");
            $group->post('/birthday', CreateBirthdayAction::class)->add("BasicAuth.BirthdayProducer");
        });
    });
};
