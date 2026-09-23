<?php

namespace App\Domain\Puzzle;

use App\Domain\Shared\File;

interface PuzzleFileStorageInterface
{
    public function getPuzzleFileNames(): array;
    public function getPuzzleFile(string $filename): File;
    public function getAdvertisementFile(): File;
}
