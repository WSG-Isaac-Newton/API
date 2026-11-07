<?php

declare(strict_types=1);

namespace App\Application\Controllers;

use App\Domain\Shared\File;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\StreamFactory;

abstract class Controller
{
    public function __construct(
        protected readonly LoggerInterface $logger,
    ) {}

    protected function respondWithJson(Response $response, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    protected function respondWithFile(Response $response, File $file): Response
    {
        $stream = (new StreamFactory)->createStream($file->contents);

		return $response
			->withHeader('Content-Type', $file->mimeType)
			->withHeader('Content-Length', (string) $file->size)
			->withBody($stream);
    }

    protected function respondNoContent(Response $response): Response
    {
        return $response->withStatus(204);
    }

    /**
     * @param string[] $errors
     */
    protected function respondBadRequest(Response $response, array $errors): Response
    {
        $response->getBody()->write(json_encode(['errors' => $errors], JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }

    protected function respondNotImplemented(Response $response): Response
    {
        return $response->withStatus(501);
    }
}
