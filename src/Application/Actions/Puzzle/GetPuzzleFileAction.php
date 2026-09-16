<?php

declare(strict_types=1);

namespace App\Application\Actions\Puzzle;

use App\Application\Actions\Action;
use App\Infrastructure\FileStorage\Puzzle\LocalPuzzleFilesReader;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response;

final class GetPuzzleFileAction extends Action
{
    public function __construct(
		LoggerInterface $logger,
		private readonly LocalPuzzleFilesReader $fileReader
	) {
		parent::__construct($logger);
	}
    
	protected function action(): Response
    {
        $filename = (string)$this->resolveArg('filename');

        $file = $this->fileReader->getPuzzleFile($filename);

        return $this->response
			->withHeader('Content-Type', $file->mimeType)
			->withHeader('Content-Length', (string) $file->size)
			->withBody($file->stream);
    }
}
