<?php

namespace App\Infrastructure\FileStorage;

final class FileNotReadableException extends \RuntimeException
{
    public function __construct(string $path)
    {
        parent::__construct("File at path '$path' is not readable.");
    }
}