<?php

declare(strict_types=1);

namespace App\Domain\Poll\Repository;

use App\Domain\Poll\Poll;
use App\Domain\Poll\Option;
use DateTimeImmutable;
use PDO;

final class PollRepository
{
    public function __construct(
        private readonly PDO $db
    ) {}

    /**
     * @return Poll[]
     */
    public function findActive(): array
    {
        // 1. Fetch all active polls (Query 1)
        $stmt = $this->db->query(
            "SELECT * FROM polls WHERE published_at <= NOW() AND (expires_at IS NULL OR expires_at >= NOW());"
        );
        $pollRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($pollRows)) {
            return [];
        }

        // 2. Extract IDs to fetch all options at once
        $pollIds = array_column($pollRows, 'poll_id');
        $placeholders = implode(',', array_fill(0, count($pollIds), '?'));

        // 3. Fetch all options for these specific polls (Query 2)
        $stmt = $this->db->prepare("SELECT * FROM options WHERE poll_id IN ($placeholders)");
        $stmt->execute($pollIds);
        $optionRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 4. Group the Option objects by poll_id
        $optionsByPoll = [];
        foreach ($optionRows as $row) {
            $pollId = (int) $row['poll_id'];
            
            $optionsByPoll[$pollId][] = new Option(
                id: (int) $row['option_id'],
                text: $row['text'],
                voteCount: (int) ($row['vote_count'] ?? 0)
            );
        }

        // 5. Assemble and return the final Poll objects
        $polls = [];
        foreach ($pollRows as $row) {
            $pollId = (int) $row['poll_id'];
            
            $polls[] = new Poll(
                id: $pollId,
                question: $row['question'],
                options: $optionsByPoll[$pollId] ?? [], // Inject options or empty array
                publishedAt: $row['published_at'] ? new DateTimeImmutable($row['published_at']) : null,
                expiresAt: $row['expires_at'] ? new DateTimeImmutable($row['expires_at']) : null
            );
        }

        return $polls;
    }
}
