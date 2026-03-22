<?php

namespace App\Application\Actions\Directory;

use App\Application\Actions\Action;
use App\Domain\Directory\EventQueueRepository;
use PDO;
use Psr\Log\LoggerInterface;

abstract class EventQueueAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        PDO $db,
        protected EventQueueRepository $eventQueue,
    ) {
        parent::__construct($logger, $db);
    }
}
