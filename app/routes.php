<?php

declare(strict_types=1);

use App\Application\Actions\Directory\CreateGroupEventAction;
use App\Application\Actions\Directory\ListEventQueueAction;
use App\Application\Actions\Directory\MarkEventStatusAction;
use App\Application\Actions\Directory\PopEventQueueAction;
use App\Application\Actions\Member\CreateBirthdayAction;
use App\Application\Actions\Member\ListBirthdaysAction;
use App\Application\Actions\Puzzle\GetAdvertisementFileAction;
use App\Application\Actions\Puzzle\GetPuzzleFileAction;
use App\Application\Actions\Puzzle\ListPuzzlesAction;
use App\Application\Controllers\PollsController;
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
            $memberRouteGroup->post('/birthday', CreateBirthdayAction::class)->add("BasicAuth.BirthdayProducer");
        });

        $routeGroup->post('/group', CreateGroupEventAction::class)->add("BasicAuth.DirectoryEventProducer");
    });

    $app->group('/directory', function (RouteGroup $routeGroup) {
        $routeGroup->group('/event-queue', function (RouteGroup $eventQueueGroup) {
            $eventQueueGroup->get('', ListEventQueueAction::class); 
            $eventQueueGroup->post('/pop', PopEventQueueAction::class);
            $eventQueueGroup->put('/{id}/mark', MarkEventStatusAction::class);
        });
    })->add("BasicAuth.DirectoryEventConsumer");

    $app->group('/polls', function ($group) {
        $group->get('', [PollsController::class, 'index']);
        $group->post('', [PollsController::class, 'store']);
        $group->get('/{poll_id}', [PollsController::class, 'show']);
        $group->put('/{poll_id}', [PollsController::class, 'update']);
        $group->delete('/{poll_id}', [PollsController::class, 'delete']);
        $group->patch('/{poll_id}/expire', [PollsController::class, 'expire']);
        // $group->get('/{poll_id}/votes/{member_id}', [VotesController::class, 'show']);
        // $group->post('/{poll_id}/votes/{member_id}', [VotesController::class, 'store']);
    });

    $app->group('/members', function (RouteGroup $routeGroup) {
        $routeGroup->get('/todays-birthdays', ListBirthdaysAction::class)->add("BasicAuth.BirthdayConsumer");
    });

    $app->group('/puzzles', function (RouteGroup $routeGroup) {
        $routeGroup->get('', ListPuzzlesAction::class);
        $routeGroup->get('/{filename}', GetPuzzleFileAction::class);
        });

    $app->get('/puzzles-advertisement', GetAdvertisementFileAction::class);
};
