<?php

declare(strict_types=1);

namespace App\Domain\Poll;

interface PollRepositoryInterface
{
    /** @return int[] */
    public function getAllPollIds(): array;

    /** @return int[] */
    public function getAllActivePollIds(): array;

    /** @return int[] */
    public function getAllExpiredPollIds(): array;

    public function getPoll(int $pollId): ?Poll;

    public function createPoll(Poll $poll): Poll;

    public function updatePoll(Poll $poll): Poll;

    public function deletePoll(int $pollId): void;
}
