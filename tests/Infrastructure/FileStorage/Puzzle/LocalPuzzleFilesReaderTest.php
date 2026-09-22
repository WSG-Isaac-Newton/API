<?php

declare(strict_types=1);

namespace Tests\Infrastructure\FileStorage\Puzzle;

use App\Infrastructure\FileStorage\Puzzle\LocalPuzzleFilesReader;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Tests\TestCase;


class LocalPuzzleFilesReaderTest extends TestCase
{
    private readonly vfsStreamDirectory $root;

    private readonly LocalPuzzleFilesReader $reader;

    public function setUp(): void
    {
        parent::setUp();

        // Global fixtures
        $this->root = vfsStream::setup('var/puzzle');
        vfsStream::newFile('config.ini')->at($this->root)->setContent("PUZZLE_ADVERTISEMENT_FILE=ad.txt");

        // System under test
        $this->reader = new LocalPuzzleFilesReader($this->root->url());
    }

    public function testGetPuzzleFileNamesArchivesStalePuzzleFolders(): void
    {
        // Arrange
        $staleFolder = vfsStream::newDirectory('2023-01-01')->at($this->root);
        file_put_contents($staleFolder->url() . '/testfile.txt', '');

        // Act
        $_ = $this->reader->getPuzzleFileNames();

        // Assert
        $this->assertFalse($this->root->hasChild('2023-01-01'));
        $this->assertTrue($this->root->hasChild('archive/2023-01-01'));
    }

    public function testGetPuzzleFileNamesReturnsEmptyArrayWhenNoFiles(): void
    {
        // Arrange
        vfsStream::newDirectory(date("Y-m-d"))->at($this->root);

        // Act
        $fileNames = $this->reader->getPuzzleFileNames();

        // Assert
        $this->assertIsArray($fileNames);
        $this->assertCount(0, $fileNames);
    }

    public function testGetPuzzleFileNamesReturnsArrayOfFileNames(): void
    {
        // Arrange
        $todaysFolder = vfsStream::newDirectory(date("Y-m-d"))->at($this->root);
        vfsStream::newFile('Crossword.txt')->at($todaysFolder);
        vfsStream::newFile('Sudoku.txt')->at($todaysFolder);

        // Act
        $fileNames = $this->reader->getPuzzleFileNames();

        // Assert
        $this->assertIsArray($fileNames);
        $this->assertContains('Crossword.txt', $fileNames);
        $this->assertContains('Sudoku.txt', $fileNames);
    }

    public function testGetAdvertisementFileReturnsFile(): void
    {
        // Arrange
        vfsStream::newFile('advertisement/ad.txt')->at($this->root);

        // Act
        $file = $this->reader->getAdvertisementFile();

        // Assert
        $this->assertNotNull($file);
    }

    public function testGetPuzzleFileReturnsFile(): void
    {
        // Arrange
        $todaysFolder = vfsStream::newDirectory(date("Y-m-d"))->at($this->root);
        vfsStream::newFile('Crossword.txt')->at($todaysFolder);
        vfsStream::newFile('Sudoku.txt')->at($todaysFolder);

        // Act
        $file = $this->reader->getPuzzleFile('Crossword.txt');

        // Assert
        $this->assertNotNull($file);
    }
}
