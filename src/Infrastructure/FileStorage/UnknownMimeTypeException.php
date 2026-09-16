<?php

namespace App\Infrastructure\FileStorage;

final class UnknownMimeTypeException extends \RuntimeException
{
    public function __construct(string $path)
    {
        parent::__construct("Unknown MIME type for file at path '$path'.");
    }
}
