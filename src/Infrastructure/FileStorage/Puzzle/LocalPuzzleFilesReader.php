<?php

declare(strict_types=1);

namespace App\Infrastructure\FileStorage\Puzzle;

use App\Infrastructure\FileStorage\File;
use App\Infrastructure\FileStorage\FileReader;
use RuntimeException;

final class LocalPuzzleFilesReader extends FileReader
{
    private readonly string $configPath;
    private readonly string $todaysDate;
    private readonly string $todaysPuzzleDirectory;
    private readonly string $archiveDirectory;
    private readonly string $defaultAdvertisementDirectory;

    public function __construct(
        private readonly string $rootDirectory,
    ) {
        $this->configPath = $this->rootDirectory . DIRECTORY_SEPARATOR . 'config.ini';
        $this->todaysDate = date("Y-m-d");
        $this->todaysPuzzleDirectory = $this->rootDirectory . DIRECTORY_SEPARATOR . $this->todaysDate;
        $this->archiveDirectory = $this->rootDirectory . DIRECTORY_SEPARATOR . 'archive';
        $this->defaultAdvertisementDirectory = $this->rootDirectory . DIRECTORY_SEPARATOR . 'advertisements';
    }

    /**
     * Get the puzzle advertisement file.
     * @throws RuntimeException if the configuration file cannot be read or the advertisement file name is not set.
     * @return File
     */
    public function getAdvertisementFile(): File
    {
        $config = $this->readConfig();

        if (!isset($config['PUZZLE_ADVERTISEMENT_FILE'])) {
            throw new RuntimeException('Puzzle advertisement file name is not set in the configuration.');
        }

        $directory = $config['PUZZLE_ADVERTISEMENT_DIRECTORY'] ?? $this->defaultAdvertisementDirectory;

        $file = $directory . DIRECTORY_SEPARATOR . $config['PUZZLE_ADVERTISEMENT_FILE'];

        return $this->read($file);
    }

    public function getPuzzleFileNames(): array
    {
        $this->deleteStalePuzzleDirectories();
        $this->createTodaysPuzzleDirectoryIfNotExist();

        if (!is_dir($this->todaysPuzzleDirectory)) {
            return [];
        }

        $files = scandir($this->todaysPuzzleDirectory);

        return array_values(array_filter($files, fn($file) => !in_array($file, ['.', '..'])));
    }

    public function getPuzzleFile(string $filename): File
    {
        $this->deleteStalePuzzleDirectories();
        $this->createTodaysPuzzleDirectoryIfNotExist();

        $path = $this->todaysPuzzleDirectory . DIRECTORY_SEPARATOR . $filename;

        return $this->read($path);
    }

    private function deleteStalePuzzleDirectories()
    {
        $this->createPuzzleArchiveDirectoryIfNotExist();

        $puzzleDirectories = preg_grep("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", scandir($this->rootDirectory));
        foreach ($puzzleDirectories as $directoryName) {
            $directoryPath = $this->rootDirectory . DIRECTORY_SEPARATOR . $directoryName;

            if (!is_dir($directoryPath)) {
                continue;
            }

            $isEmpty = function ($directoryPath) {
                $content = array_diff(scandir($directoryPath), array('.', '..', '@eaDir', 'Thumbs.db'));
                return count($content) == 0;
            };
            if ($isEmpty($directoryPath)) {
                rmdir($directoryPath);
                continue;
            }

            if ($directoryName != $this->todaysDate) {
                rename($directoryPath, $this->archiveDirectory . DIRECTORY_SEPARATOR . $directoryName);
                continue;
            }
        }
    }

    private function createPuzzleArchiveDirectoryIfNotExist()
    {
        if (!is_dir($this->archiveDirectory)) {
            mkdir($this->archiveDirectory, 0777, true);
        }
    }

    private function createTodaysPuzzleDirectoryIfNotExist()
    {
        if (!is_dir($this->todaysPuzzleDirectory)) {
            mkdir($this->todaysPuzzleDirectory, 0777, true);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function readConfig(): array
    {
        $config = parse_ini_file($this->configPath, true, INI_SCANNER_TYPED);

        if ($config === false) {
            throw new RuntimeException(sprintf('Unable to read puzzle configuration: %s', $this->configPath));
        }

        return $config;
    }
}
