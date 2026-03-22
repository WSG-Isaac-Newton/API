<?php

namespace App\Domain\Directory;

// Violation of dependency rule, but we need the trigger enum to create the event from the webhook data.
// We can refactor this later if needed.
use App\Domain\Congressus\Webhook\Trigger;

interface Event {
    public function getId(): ?int;
    public function getTrigger(): Trigger;
    public function getGroupId(): int;
    public function getGroupName(): string;
    public function getGroupBreadcrumbs(): string;
    public function getMemberId(): ?int;
}
