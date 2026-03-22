<?php

namespace App\Infrastructure\Persistence\Directory;

use App\Domain\Directory\EventQueueRepository;

final readonly class PdoEventQueueRepository implements EventQueueRepository
{
    private string $tableName;

    public function __construct(
        private \PDO $db,
    ) {
        $this->tableName = 'congressus_webhooks_event_queue';
    }

    public function list(): array
    {
        $stmt = $this->db->query("SELECT * FROM $this->tableName");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function pop(): ?array
    {
        $stmt = $this->db->query("SELECT * FROM $this->tableName ORDER BY created_at ASC LIMIT 1");
        $event = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($event) {
            $deleteStmt = $this->db->prepare("DELETE FROM $this->tableName WHERE id = :id");
            $deleteStmt->execute([':id' => $event['id']]);
        }

        return $event ?? null;
    }
}
