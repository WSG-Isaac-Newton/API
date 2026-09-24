<?php

namespace App\Domain\Poll;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;
use JsonSerializable;

#[Entity(database: 'poll')]
class Option implements JsonSerializable
{
    private int $voteCount = 0;
    
    public function __construct(
        #[Column(type: "primary")]
        private readonly int $id,

        #[Column(type: "string")]
        private readonly string $text,

        #[BelongsTo(target: Poll::class, innerKey: 'poll_id', outerKey: 'id')]
        private readonly Poll $poll,
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
 