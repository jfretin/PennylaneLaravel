<?php

namespace Ashraam\PennylaneLaravel\Tests\Http;

use Ashraam\PennylaneLaravel\Events\ApiCallTelemetryCaptured;
use Ashraam\PennylaneLaravel\Http\Middleware\ApiTelemetryMiddleware;
use Ashraam\PennylaneLaravel\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class ApiTelemetryMiddlewareTest extends TestCase
{
    /**
     * @var array<int, ApiCallTelemetryCaptured>
     */
    private array $capturedEvents = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->capturedEvents = [];
        app('events')->listen(ApiCallTelemetryCaptured::class, function (ApiCallTelemetryCaptured $event): void {
            $this->capturedEvents[] = $event;
        });
    }

    public function testTelemetryMarksInitialAttemptAsNotRetried(): void
    {
        $client = $this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ]);

        $client->request('GET', '/v2/test');

        $payload = $this->capturedEvents[0]->toArray();

        $this->assertSame(0, $payload['retry_attempt']);
        $this->assertFalse($payload['was_retried']);
    }

    public function testTelemetryMarksRetriedAttemptExplicitly(): void
    {
        $client = $this->makeClient([
            new Response(200, [], json_encode(['ok' => true])),
        ]);

        $client->request('GET', '/v2/test', [
            'pennylane_rate_limit_retries' => 1,
        ]);

        $payload = $this->capturedEvents[0]->toArray();

        $this->assertSame(1, $payload['retry_attempt']);
        $this->assertTrue($payload['was_retried']);
    }

    /**
     * @param array<int, Response> $responses
     */
    private function makeClient(array $responses): Client
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(new ApiTelemetryMiddleware('v2'), 'pennylane_api_telemetry');

        return new Client([
            'base_uri' => 'https://example.test/',
            'handler' => $stack,
        ]);
    }
}
