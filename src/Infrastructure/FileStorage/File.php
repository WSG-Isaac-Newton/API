<?php

declare(strict_types=1);

namespace App\Infrastructure\FileStorage;

use Psr\Http\Message\StreamInterface;

final class File
{
    public function __construct(
        public readonly StreamInterface $stream,
        public readonly string $mimeType,
        public readonly int $size,
    ) {}
}
