<?php

namespace App\Domain\Directory;

interface EventQueueRepository
{
    public function list(): array;
    public function pop(): ?array;
}
