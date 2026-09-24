<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Poll;

use App\Domain\Poll\VoteRepositoryInterface;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\Injection\Fragment;

final class VoteRepository implements VoteRepositoryInterface
{
    public function __construct(
        private readonly DatabaseInterface $database,
    ) {}

    public function countByPollId(int $pollId): array
    {
        $rows = $this->database
            ->select()
            ->from('votes')
            ->columns('option_id', new Fragment('COUNT(*) AS count'))
            ->where('poll_id', $pollId)
            ->groupBy('option_id')
            ->fetchAll();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['option_id']] = (int) $row['count'];
        }

        return $counts;
    }
}
