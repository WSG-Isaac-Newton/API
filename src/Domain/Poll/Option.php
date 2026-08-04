<?php

declare(strict_types=1);

namespace App\Domain\Poll;

use JsonSerializable;

class Option implements JsonSerializable
{
    public function __construct(
        private readonly int $id,
        private readonly string $text,
        private readonly int $voteCount = 0
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getVoteCount(): int
    {
        return $this->voteCount;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'         => $this->id,
            'text'       => $this->text,
            'vote_count' => $this->voteCount,
        ];
    }
}
