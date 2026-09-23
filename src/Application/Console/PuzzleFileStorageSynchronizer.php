<?php

namespace App\Application\Console;

use App\Domain\Puzzle\PuzzleFileStorageInterface;

class PuzzleFileStorageSynchronizer implements SchedulerInterface
{
    public function __construct(
        private readonly PuzzleFileStorageInterface $storage,
    ) {}

    #[\Override]
    public function run(): void {
        $this->storage->synchronize();
    }
}
