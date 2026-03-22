<?php

namespace App\Application\Actions\Directory;

use Psr\Http\Message\ResponseInterface as Response;

final class ListEventQueueAction extends EventQueueAction
{
    protected function action(): Response
    {
        $events = $this->eventQueue->list();
        return $this->respondWithData($events);
    }
}
