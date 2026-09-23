<?php

namespace App\Application\Actions\Poll;

use App\Application\Actions\Action;
use App\Infrastructure\Persistence\Poll\PollRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

final class ListActivePollsAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private readonly PollRepository $pollRepository
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $polls = $this->pollRepository->findActive();

        return $this->respondWithData($polls);
    }
}
