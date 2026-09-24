<?php

declare(strict_types=1);

namespace App\Domain\Poll;

interface VoteRepositoryInterface
{
    /** @return array<int, int> Counts keyed by option ID. */
    public function countByPollId(int $pollId): array;
}
