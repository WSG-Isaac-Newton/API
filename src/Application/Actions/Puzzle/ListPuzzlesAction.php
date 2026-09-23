<?php

declare(strict_types=1);

namespace App\Application\Actions\Puzzle;

use App\Application\Actions\Action;
use App\Domain\Puzzle\PuzzleFileStorageInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

final class ListPuzzlesAction extends Action
{
	public function __construct(
		LoggerInterface $logger,
		private readonly PuzzleFileStorageInterface $storage
	) {
		parent::__construct($logger);
	}

	protected function action(): Response
	{
		$data = $this->storage->getPuzzleFileNames();

		return $this->respondWithData($data);
	}
}
