<?php

namespace App\Application\Actions\Directory;

use App\Domain\Directory\EventStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

final class MarkEventStatusAction extends EventQueueAction
{
    protected function action(): Response
    {
        $id = (int)$this->resolveArg('id');
        $status = $this->getStatus();

        $this->eventQueue->mark($id, $status);

        return $this->respondOk();
    }

    protected function getStatus(): EventStatus
    {
        try {
            $statusValue = $this->getFormData()['status'] ?? '';
            return EventStatus::from($statusValue);
        } catch (\Throwable $e) {
            throw new HttpBadRequestException($this->request, 'Invalid status value');
        }
    }
}
