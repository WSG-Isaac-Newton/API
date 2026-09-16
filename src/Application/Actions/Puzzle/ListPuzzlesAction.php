<?php

declare(strict_types=1);

namespace App\Application\Actions\Puzzle;

use App\Application\Actions\Action;
use App\Infrastructure\FileStorage\Puzzle\LocalPuzzleFilesReader;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

final class ListPuzzlesAction extends Action
{
	public function __construct(
		LoggerInterface $logger,
		private readonly LocalPuzzleFilesReader $fileReader
	) {
		parent::__construct($logger);
	}

	protected function action(): Response
	{
		$data = $this->fileReader->getPuzzleFileNames();
		return $this->respondWithData($data);
	}
}
