<?php

declare(strict_types=1);

namespace App\Infrastructure\FileStorage\Puzzle;

use App\Infrastructure\FileStorage\File;
use App\Infrastructure\FileStorage\FileReader;
use RuntimeException;

define("DATE_TODAY_STRING", date("Y-m-d"));
define("VAR_PUZZLES_PATH", PROJECT_ROOT . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'puzzle' . DIRECTORY_SEPARATOR);
define("VAR_PUZZLES_TODAY_PATH", VAR_PUZZLES_PATH . DATE_TODAY_STRING . DIRECTORY_SEPARATOR);
define("VAR_PUZZLES_ARCHIVE_PATH", PROJECT_ROOT . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'puzzle' . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR);

final class LocalPuzzleFilesReader extends FileReader
{
    public function __construct(
        private readonly string $configPath,
        private readonly string $defaultAdvertisementFolder
    ) {
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

        $advertisementFolder = $config['PUZZLE_ADVERTISEMENT_FOLDER'] ?? $this->defaultAdvertisementFolder;

        $path = $advertisementFolder . DIRECTORY_SEPARATOR . $config['PUZZLE_ADVERTISEMENT_FILE'];

        return $this->read($path);
    }

    public function getPuzzleFileNames(): array
    {
        $this->deleteStalePuzzleFolders();
        $this->createTodaysPuzzleFolderIfNotExist();

        if (!is_dir(VAR_PUZZLES_TODAY_PATH)) {
            return [];
        }

        $files = scandir(VAR_PUZZLES_TODAY_PATH);
        return array_values(array_filter($files, fn($file) => !in_array($file, ['.', '..'])));
    }

    public function getPuzzleFile(string $filename): File
    {
        $this->deleteStalePuzzleFolders();
        $this->createTodaysPuzzleFolderIfNotExist();

        $path = VAR_PUZZLES_TODAY_PATH . $filename;

        return $this->read($path);
    }

    private function deleteStalePuzzleFolders()
    {
        $this->createPuzzleArchiveFolderIfNotExist();
        
        $puzzleFolderNames = preg_grep("/[0-9]{4}-[0-9]{2}-[0-9]{2}/", scandir(VAR_PUZZLES_PATH));
        echo(count(scandir(VAR_PUZZLES_PATH)));
        foreach ($puzzleFolderNames as $folderName) {
            $folderPath = VAR_PUZZLES_PATH . $folderName;

            if (!is_dir($folderPath)) {
                continue;
            }

            $isEmpty = function ($folderPath) {
                $content = array_diff(scandir($folderPath), array('.', '..', '@eaDir', 'Thumbs.db'));
                return count($content) == 0;
            };
            if ($isEmpty($folderPath)) {
                rmdir($folderPath);
                continue;
            }

            if ($folderName != DATE_TODAY_STRING) {
                rename($folderPath, VAR_PUZZLES_ARCHIVE_PATH . $folderName);
                continue;
            }
        }
    }

    private function createPuzzleArchiveFolderIfNotExist()
    {
        if (!is_dir(VAR_PUZZLES_ARCHIVE_PATH)) {
            mkdir(VAR_PUZZLES_ARCHIVE_PATH);
        }
    }

    private function createTodaysPuzzleFolderIfNotExist()
    {
        if (!is_dir(VAR_PUZZLES_TODAY_PATH)) {
            mkdir(VAR_PUZZLES_TODAY_PATH);
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
