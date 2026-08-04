<?php

declare(strict_types=1);

namespace App\Domain\Poll;

use JsonSerializable;
use DateTimeImmutable;

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
        private readonly int $id,
        private readonly string $question,
        private readonly array $options,
        private readonly ?DateTimeImmutable $publishedAt = null,
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
