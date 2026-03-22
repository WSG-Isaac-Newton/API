<?php

namespace App\Application\Actions\Directory;

use App\Application\Actions\Action;
use App\Domain\Directory\EventQueueRepository;

abstract class EventQueueAction extends Action
{
    public function __construct(
        protected EventQueueRepository $eventQueue,
    ) {}
}
