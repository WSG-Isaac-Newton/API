<?php

namespace App\Domain\Poll;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use DateTimeImmutable;
use JsonSerializable;
use Cycle\Annotated\Annotation\Relation\HasMany;

#[Entity(database: 'poll')]
class Poll implements JsonSerializable
{
    /**
     * @param int $id
     * @param string $question
     * @param array<Option> $options
     * @param DateTimeImmutable|null $publishedAt
     * @param DateTimeImmutable|null $expiresAt
     */
    public function __construct(
        #[Column(type: "primary")]
        private readonly int $id,

        #[Column(type: "string")]
        private readonly string $question,

        #[HasMany(target: Option::class)]
        private readonly array $options,
        
        #[Column(type: "datetime")]
        private readonly ?DateTimeImmutable $publishedAt = null,
        
        #[Column(type: "datetime", nullable: true)]
        private readonly ?DateTimeImmutable $expiresAt = null
    ) {}

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /** @param array<int|string, int|string> $counts */
    public function setVoteCounts(array $counts): void
    {
        foreach ($this->getOptions() as $option) {
            $option->setVoteCount((int) ($counts[$option->getId()] ?? 0));
        }

    }

    public function jsonSerialize(): array
    {
        return [
            'id'           => $this->id,
            'question'     => $this->question,
            'published_at' => $this->publishedAt?->format('Y-m-d H:i:s'),
            'expires_at'   => $this->expiresAt?->format('Y-m-d H:i:s'),
            'options'      => $this->options,
        ];
    }
}
