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

    public function getPollById(int $pollId): ?Poll;

    public function createPoll(Poll $poll): void;

    public function updatePoll(Poll $poll): void;

    public function deletePoll(int $pollId): void;
}
