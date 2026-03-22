<?php

namespace App\Application\Actions\Directory;

use Psr\Http\Message\ResponseInterface as Response;

final class PopEventQueueAction extends EventQueueAction
{
    protected function action(): Response
    {
        $event = $this->eventQueue->pop();
        return $this->respondWithData($event);
    }
}
