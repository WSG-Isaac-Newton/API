<?php

namespace App\Domain\Shared;

final class File
{
    public function __construct(
        public readonly string $filename,
        public readonly string $mimeType,
        public readonly int $size,
        public readonly string $contents,
    ) {}
}
