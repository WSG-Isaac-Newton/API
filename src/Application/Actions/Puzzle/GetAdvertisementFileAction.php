<?php

declare(strict_types=1);

namespace App\Application\Actions\Puzzle;

use App\Application\Actions\Action;
use App\Domain\Puzzle\PuzzleFileStorageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\StreamFactory;

final class GetAdvertisementFileAction extends Action
{
	public function __construct(
		LoggerInterface $logger,
		private readonly PuzzleFileStorageInterface $storage
	) {
		parent::__construct($logger);
	}

	protected function action(): ResponseInterface
	{
		$file = $this->storage->getAdvertisementFile();

		$stream = (new StreamFactory())->createStream($file->contents);

		return $this->response
			->withHeader('Content-Type', $file->mimeType)
			->withHeader('Content-Length', (string) $file->size)
			->withBody($stream);
	}
}
