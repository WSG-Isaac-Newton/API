<?php

declare(strict_types=1);

namespace App\Application\Actions\Poll;

use App\Application\Actions\Action;
use App\Domain\Poll\Repository\PollRepository;
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
        $this->logger->info("Active polls were viewed.");

        $polls = $this->pollRepository->findActive();

        foreach ($polls as &$poll) {
            $poll['options'] = $this->pollRepository->findOptionsByPollId((int) $poll['poll_id']);
        }

        return $this->respondWithData($polls);
    }
}