<?php

declare(strict_types=1);

namespace App\Test\TestCase\Action;

use Tests\TestCase;

class ListActivePollsActionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $app = $this->getAppInstance();
        $pdo = $app->getContainer()->get('db.poll');

        $pdo->exec('DELETE FROM options');
        $pdo->exec('DELETE FROM polls');
    }

    public function testActivePollsReturnsPublishedUnexpiredPolls(): void
    {
        $app = $this->getAppInstance();
        $pdo = $app->getContainer()->get('db.poll');

        $pdo->exec("
            INSERT INTO polls (poll_id, question, published_at, expires_at)
            VALUES (1, 'Favorite color?', '2024-01-01 00:00:00', NULL)
        ");
        $pdo->exec("
            INSERT INTO options (option_id, poll_id, text) VALUES
            (1, 1, 'Red'),
            (2, 1, 'Blue')
        ");

        $request = $this->createRequest('GET', '/polls/active');
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);

        $this->assertCount(1, $body['data']);
        $this->assertSame('Favorite color?', $body['data'][0]['question']);
        $this->assertCount(2, $body['data'][0]['options']);
    }

    public function testExpiredPollIsExcluded(): void
    {
        $app = $this->getAppInstance();
        $pdo = $app->getContainer()->get('db.poll');

        $pdo->exec("
            INSERT INTO polls (poll_id, question, published_at, expires_at)
            VALUES (2, 'Old poll', '2020-01-01 00:00:00', '2020-02-01 00:00:00')
        ");

        $request = $this->createRequest('GET', '/polls/active');
        $response = $app->handle($request);

        $body = json_decode((string) $response->getBody(), true);
        $this->assertCount(0, $body['data']);
    }
}
