<?php

declare(strict_types=1);

namespace App\Domain\Poll;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;

#[Entity(database: 'poll')]
final class Vote
{
    public function __construct(
        #[Column(type: 'primary')]
        private readonly int $id,

        #[BelongsTo(target: Option::class, innerKey: 'option_id', outerKey: 'id')]
        private readonly int $option,

        #[Column(type: 'integer')]
        private readonly int $userId,

        #[Column(type: "datetime")]
        private readonly ?\DateTimeImmutable $publishedAt = null,
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }
}
