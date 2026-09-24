<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Poll;

use App\Domain\Poll\VoteRepositoryInterface;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\Injection\Fragment;

final class VoteRepository implements VoteRepositoryInterface
{
    private const TABLE = 'votes';
    private const OPTION_ID_COLUMN = 'option_id';
    private const POLL_ID_COLUMN = 'poll_id';
    private const COUNT_ALIAS = 'count';

    public function __construct(
        private readonly DatabaseInterface $database,
    ) {}

    public function countByPollId(int $pollId): array
    {
        $rows = $this->database
            ->select()
            ->from(self::TABLE)
            ->columns(
                self::OPTION_ID_COLUMN,
                new Fragment('COUNT(*) AS ' . self::COUNT_ALIAS),
            )
            ->where(self::POLL_ID_COLUMN, $pollId)
            ->groupBy(self::OPTION_ID_COLUMN)
            ->fetchAll();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row[self::OPTION_ID_COLUMN]] = (int) $row[self::COUNT_ALIAS];
        }

        return $counts;
    }
}
