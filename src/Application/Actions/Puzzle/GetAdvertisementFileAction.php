<?php

declare(strict_types=1);

namespace App\Application\Actions\Puzzle;

use App\Application\Actions\Action;
use App\Infrastructure\FileStorage\Puzzle\LocalPuzzleFilesReader;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

final class GetAdvertisementFileAction extends Action
{
	public function __construct(
		LoggerInterface $logger,
		private readonly LocalPuzzleFilesReader $fileReader
	) {
		parent::__construct($logger);
	}

	protected function action(): ResponseInterface
	{
		$file = $this->fileReader->getAdvertisementFile();

		return $this->response
			->withHeader('Content-Type', $file->mimeType)
			->withHeader('Content-Length', (string) $file->size)
			->withBody($file->stream);
	}
}
