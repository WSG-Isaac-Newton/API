<?php

namespace App\Application\Middleware\Auth;

use App\Domain\Auth\BasicAuthRepository;
use App\Domain\Auth\Scope;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class BasicAuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly BasicAuthRepository $repository,
        private readonly Scope $scope,
    ) {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        try {
            $passwordHash = $this->repository->getPasswordHash($this->scope);

            if ($passwordHash === null) {
                return $this->authorize($request, $handler);
            }

            if (!$this->verifyPassword($request, $passwordHash)) {
                return $this->unauthorized();
            }
        } catch (\Throwable) {
            return $this->unauthorized();
        }

        return $this->authorize($request, $handler);
    }

    private function unauthorized(): Response
    {
        return $this->responseFactory
            ->createResponse(401)
            ->withHeader('WWW-Authenticate', 'Basic realm="API"');
    }

    private function authorize(Request $request, RequestHandler $handler): Response
    {
        return $handler->handle($request);
    }

    private function verifyPassword(Request $request, string $passwordHash): bool
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!str_starts_with($authHeader, 'Basic ')) {
            return false;
        }

        $encoded = substr($authHeader, 6);
        $decoded = base64_decode($encoded);
        $password = explode(':', $decoded, 2)[1];

        return password_verify($password, $passwordHash);
    }
}
