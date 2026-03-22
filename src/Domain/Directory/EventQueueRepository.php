<?php

namespace App\Domain\Directory;

interface EventQueueRepository
{
    public function list(): array;
    public function pop(): ?Event;
    public function mark(int $id, EventStatus $status): void;
    public function push(Event $event): void;
}
