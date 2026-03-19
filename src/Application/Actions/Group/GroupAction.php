<?php

namespace App\Application\Actions\Group;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;

final class GroupAction extends Action
{
    protected function action(): Response
    {
        $this->logger->info("Group webhook received: " . json_encode($this->getFormData()));
        return $this->respondOk();
    }
}
