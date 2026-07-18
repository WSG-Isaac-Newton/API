<?php

declare(strict_types=1);

namespace App\Domain\Poll\Repository;

final class PollRepository
{
    public function __construct(
        private readonly \PDO $db
    ) {
    }

    public function findActive(): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM polls WHERE published_at <= NOW() AND (expires_at IS NULL OR expires_at >= NOW());"
        );

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findOptionsByPollId(int $pollId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM options WHERE poll_id = :poll_id;");
        $stmt->execute(['poll_id' => $pollId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}