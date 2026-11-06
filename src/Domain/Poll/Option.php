<?php

declare(strict_types=1);

namespace App\Domain\Poll;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

#[Entity(database: 'poll')]
class Option implements \JsonSerializable
{
    private int $voteCount = 0;

    public function __construct(
        #[Column(type: "string")]
        private readonly string $text,

        #[Column(type: "primary")]
        private ?int $id = null,
        
        #[Column(type: "integer", name: "poll_id")]
        private ?int $pollId = null,
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

    public function setVoteCount(int $voteCount): void
    {
        $this->voteCount = $voteCount;
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
