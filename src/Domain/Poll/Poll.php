<?php

declare(strict_types=1);

namespace App\Domain\Poll;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\HasMany;

#[Entity(database: 'poll')]
class Poll implements \JsonSerializable
{
    /**
     * @param int $id
     * @param string $question
     * @param array<Option> $options
     * @param \DateTimeImmutable $publishedAt
     * @param ?\DateTimeImmutable $expiresAt
     */
    private function __construct(
        #[Column(type: "string")]
        private string $question,

        #[HasMany(target: Option::class, innerKey: 'id', outerKey: 'pollId')]
        private array $options,

        #[Column(type: "datetime")]
        private \DateTimeImmutable $publishedAt,
        
        #[Column(type: "datetime", nullable: true)]
        private ?\DateTimeImmutable $expiresAt = null,
        
        #[Column(type: "primary")]
        private ?int $id = null,
    ) {}

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getPublishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }

    public function expire(): void
    {
        $this->setExpiresAt(new \DateTimeImmutable);
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
            'published_at' => $this->publishedAt->format(\DateTimeInterface::ATOM),
            'expires_at'   => $this->expiresAt?->format(\DateTimeInterface::ATOM),
            'options'      => $this->options,
        ];
    }

    public static function create(
        string $question,
        array $optionTexts,
        ?\DateTimeImmutable $publishedAt = null,
        ?\DateTimeImmutable $expiresAt = null,
    ): self
    {
        if (count($optionTexts) < 2) {
            throw new \InvalidArgumentException('At least 2 options required');
        }

        $options = [];
        foreach ($optionTexts as $optionText) {
            if ($optionText === '') {
                throw new \InvalidArgumentException('Option text is required');
            }
            $options[] = new Option(text: $optionText);
        }

        $publishedAt ??= new \DateTimeImmutable();

        return new self($question, $options, $publishedAt, $expiresAt);
    }
}
