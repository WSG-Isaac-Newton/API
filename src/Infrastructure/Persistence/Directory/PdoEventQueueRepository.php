<?php

namespace App\Infrastructure\Persistence\Directory;

use App\Domain\Directory\Event;
use App\Domain\Directory\EventQueueRepository;
use App\Domain\Directory\EventStatus;
use App\Infrastructure\Factory\DirectoryEventFactory;

final readonly class PdoEventQueueRepository implements EventQueueRepository
{
    private string $tableName;

    public function __construct(
        private \PDO $db,
    ) {
        $this->tableName = 'directory_event_queue';
    }

    public function list(): array
    {
        $stmt = $this->db->query("SELECT * FROM $this->tableName WHERE `event_status` = 'pending' ORDER BY created_at ASC");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function pop(): ?Event
    {
        $stmt = $this->db->query("SELECT * FROM $this->tableName WHERE `event_status` = 'pending' ORDER BY created_at ASC LIMIT 1");
        $event = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($event) {
            $stmt = $this->db->prepare("UPDATE $this->tableName SET `event_status` = 'processing' WHERE id = :id");
            $stmt->execute([':id' => $event['id']]);
            return DirectoryEventFactory::createFromDatabaseRecord($event);
        }

        return null;
    }

    public function mark(int $id, EventStatus $status): void
    {
        $stmt = $this->db->prepare("UPDATE $this->tableName SET `event_status` = :eventStatus WHERE id = :id");
        $stmt->execute([':eventStatus' => $status->value, ':id' => $id]);
    }

    public function push(Event $event): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO $this->tableName (`event_trigger`, `group_id`, `group_name`, `group_breadcrumbs`, `member_id`)
            VALUES (:trigger, :groupId, :groupName, :groupBreadcrumbs, :memberId);"
        );
        $stmt->execute([
            ':trigger' => $event->getTrigger()->value,
            ':groupId' => $event->getGroupId(),
            ':groupName' => $event->getGroupName(),
            ':groupBreadcrumbs' => $event->getGroupBreadcrumbs(),
            ':memberId' => $event->getMemberId(),
        ]);
    }
}
