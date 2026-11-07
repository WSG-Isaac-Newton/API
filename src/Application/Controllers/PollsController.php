<?php

namespace App\Application\Controllers;

use App\Domain\Poll\Poll;
use App\Domain\Poll\PollRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as v;

final class PollsController extends Controller
{
    public function __construct(
        LoggerInterface $logger,
        private readonly PollRepositoryInterface $polls,
    ) {
        parent::__construct($logger);
    }

    /**
     * GET /polls
     * GET /polls?status=active
     * GET /polls?status=expired
     */
    public function index(Request $request, Response $response): Response
    {
        $status = $request->getQueryParams()['status'] ?? null;

        $polls = match ($status) {
            'active'  => $this->polls->getAllActivePollIds(),
            'expired' => $this->polls->getAllExpiredPollIds(),
            null      => $this->polls->getAllPollIds(),
            default   => throw new \InvalidArgumentException("Invalid status: $status"),
        };

        return $this->respondWithJson($response, $polls);
    }

    /**
     * POST /polls
     */
    public function store(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();

        $question = $data['question'] ?? '';
        $optionTexts = $data['options'] ?? [];
        $publishedAt = !empty($data['published_at']) ? new \DateTimeImmutable($data['published_at']) : null;
        $expiresAt = !empty($data['expires_at']) ? new \DateTimeImmutable($data['expires_at']) : null;

        $poll = Poll::create($question, $optionTexts, $publishedAt, $expiresAt);

        $poll = $this->polls->createPoll($poll);

        return $this->respondWithJson($response, $poll, 201);
    }

    /**
     * GET /polls/{poll_id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $poll = $this->polls->getPollById((int) $args['poll_id']);

        return $this->respondWithJson($response, $poll);
    }

    /**
     * PATCH /polls/{poll_id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $validator = v::key(
            'question',
            v::stringType(),
            false
        )->key(
            'published_at',
            v::dateTime(\DateTime::RFC3339),
            false
        )->key(
            'expires_at',
            v::optional(
                v::dateTime(\DateTime::RFC3339)
            ),
            false
        );

        $data = (array) $request->getParsedBody();

        try {
            $validator->assert($data);
        } catch (NestedValidationException $e) {
            $errors = $e->getMessages();
            return $this->respondBadRequest($response, $errors);
        }

        $poll = $this->polls->getPollById((int) $args['poll_id']);

        if (array_key_exists('question', $data)) $poll->setQuestion($data['question']);

        if (array_key_exists('published_at',$data)) $poll->setPublishedAt(new \DateTimeImmutable($data['published_at']));

        if (array_key_exists('expires_at', $data)) {
            is_null($data['expires_at'])
            ? $poll->setExpiresAt(null)
            : $poll->setExpiresAt(new \DateTimeImmutable($data['expires_at']));
        }

        $poll = $this->polls->updatePoll($poll);

        return $this->respondWithJson($response, $poll, 200);
    }

    /**
     * DELETE /polls/{poll_id}
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $this->polls->deletePoll((int) $args['poll_id']);

        return $response->withStatus(204);
    }

    /**
     * PATCH /polls/{poll_id}/expire
     */
    public function expire(Request $request, Response $response, array $args): Response
    {
        return $this->respondNotImplemented($response);
    }
}
