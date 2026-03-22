<?php

namespace App\Domain\Directory;

use App\Domain\Congressus\Webhook\Trigger;

final readonly class GroupEvent implements Event
{
    public function __construct(
        public ?int $id,
        public Trigger $trigger,
        public int $groupId,
        public string $groupName,
        public string $groupBreadcrumbs,
        public ?int $memberId = null,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrigger(): Trigger
    {
        return $this->trigger;
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function getGroupName(): string
    {
        return $this->groupName;
    }

    public function getGroupBreadcrumbs(): string
    {
        return $this->groupBreadcrumbs;
    }

    public function getMemberId(): ?int
    {
        return $this->memberId;
    }
}
