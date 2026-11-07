<?php

namespace App\Application\Controllers;

use App\Domain\Puzzle\PuzzleFileStorageInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

final class PuzzlesController extends Controller
{
    public function __construct(
        LoggerInterface $logger,
        private readonly PuzzleFileStorageInterface $puzzles,
    ) {
        parent::__construct($logger);
    }

    /**
     * GET /puzzles
     */
    public function index(Request $request, Response $response): Response
    {
        $data = $this->puzzles->getPuzzleFileNames();

        return $this->respondWithJson($response, $data);
    }

    /**
     * GET /puzzles/advertisement
     */
    public function advertisement(Request $request, Response $response): Response
    {
		$file = $this->puzzles->getAdvertisementFile();

        return $this->respondWithFile($response, $file);
    }

    /**
     * GET /puzzles/{fileName}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $fileName = $args['fileName'];

        $validFileNames = $this->puzzles->getPuzzleFileNames();

        if (!in_array($fileName, $validFileNames)) return $this->respondBadRequest($response, ["Invalid file name"]);

		$file = $this->puzzles->getPuzzleFile($fileName);

		return $this->respondWithFile($response, $file);
    }
}
