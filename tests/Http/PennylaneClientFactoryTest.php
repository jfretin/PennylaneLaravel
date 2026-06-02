<?php

namespace Ashraam\PennylaneLaravel\Tests\Http;

use Ashraam\PennylaneLaravel\Http\PennylaneClientFactory;
use Ashraam\PennylaneLaravel\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use ReflectionProperty;

class PennylaneClientFactoryTest extends TestCase
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $history = [];

    protected function setUp(): void
    {
        parent::setUp();

        config_set([
            'pennylane-laravel' => array_replace_recursive(
                require dirname(__DIR__, 2) . '/config/config.php',
                [
                    'rate_limit' => [
                        'enabled' => true,
                        'max_retries' => 2,
                        'jitter_ms' => 0,
                        'max_delay_ms' => 0,
                        'fallback_delay_ms' => 0,
                    ],
                ]
            ),
        ]);
    }

    public function testRetries429UsingRetryAfterHeader(): void
    {
        $client = $this->makeClient([
            new Response(429, ['retry-after' => '0'], 'rate limited'),
            new Response(200, [], json_encode(['ok' => true])),
        ], true, 'v2');

        $response = $client->request('GET', '/v2/test');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(2, $this->history);
    }

    public function testRetries429UsingRateLimitResetHeader(): void
    {
        $client = $this->makeClient([
            new Response(429, ['ratelimit-reset' => (string) (time() + 1)], 'rate limited'),
            new Response(200, [], json_encode(['ok' => true])),
        ], true, 'v2');

        $response = $client->request('HEAD', '/v2/test');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(2, $this->history);
    }

    public function testFallsBackToConfiguredDelayWhenHeadersAreMissing(): void
    {
        config_set([
            'pennylane-laravel' => array_replace_recursive(
                require dirname(__DIR__, 2) . '/config/config.php',
                [
                    'rate_limit' => [
                        'enabled' => true,
                        'max_retries' => 1,
                        'jitter_ms' => 0,
                        'max_delay_ms' => 0,
                        'fallback_delay_ms' => 0,
                    ],
                ]
            ),
        ]);

        $client = $this->makeClient([
            new Response(429, [], 'rate limited'),
            new Response(200, [], json_encode(['ok' => true])),
        ], true, 'v2');

        $response = $client->request('GET', '/v2/test');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(2, $this->history);
    }

    public function testStopsRetryingAfterConfiguredMaxRetries(): void
    {
        $client = $this->makeClient([
            new Response(429, ['retry-after' => '0'], 'rate limited'),
            new Response(429, ['retry-after' => '0'], 'rate limited'),
            new Response(429, ['retry-after' => '0'], 'rate limited'),
        ], true, 'v2');

        $this->expectException(ClientException::class);

        try {
            $client->request('GET', '/v2/test');
        } finally {
            $this->assertCount(3, $this->history);
        }
    }

    public function testDoesNotRetryPostRequests(): void
    {
        $client = $this->makeClient([
            new Response(429, ['retry-after' => '0'], 'rate limited'),
        ], true, 'v2');

        $this->expectException(ClientException::class);

        try {
            $client->request('POST', '/v2/test');
        } finally {
            $this->assertCount(1, $this->history);
        }
    }

    public function testRetriesPutRequests(): void
    {
        $client = $this->makeClient([
            new Response(429, ['retry-after' => '0'], 'rate limited'),
            new Response(200, [], json_encode(['ok' => true])),
        ], true, 'v2');

        $response = $client->request('PUT', '/v2/test');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertCount(2, $this->history);
    }

    public function testV2StackPlacesRateLimitMiddlewareBeforeTelemetry(): void
    {
        $client = PennylaneClientFactory::createClient([
            'base_uri' => 'https://example.test/',
        ], true, 'v2');

        $names = $this->handlerStackNames($client);

        $this->assertContains('pennylane_api_rate_limit', $names);
        $this->assertContains('pennylane_api_telemetry', $names);
        $this->assertLessThan(
            array_search('pennylane_api_telemetry', $names, true),
            array_search('pennylane_api_rate_limit', $names, true)
        );
    }

    public function testV1ClientDoesNotIncludeRateLimitMiddleware(): void
    {
        $client = PennylaneClientFactory::createClient([
            'base_uri' => 'https://example.test/',
        ], false, 'v1');

        $names = $this->handlerStackNames($client);

        $this->assertNotContains('pennylane_api_rate_limit', $names);
        $this->assertFalse(PennylaneClientFactory::shouldEnableRateLimitMiddleware('v1'));
    }

    /**
     * @param array<int, Response> $responses
     * @return Client
     */
    private function makeClient(array $responses, bool $enableTelemetry, string $apiVersion): Client
    {
        $this->history = [];
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($this->history), 'history');

        $client = PennylaneClientFactory::createClient([
            'base_uri' => 'https://example.test/',
            'handler' => $stack,
        ], $enableTelemetry, $apiVersion);

        return $client;
    }

    /**
     * @return array<int, string|null>
     */
    private function handlerStackNames(Client $client): array
    {
        $handler = $client->getConfig('handler');
        $property = new ReflectionProperty(HandlerStack::class, 'stack');
        $property->setAccessible(true);
        $stack = $property->getValue($handler);

        return array_map(static fn (array $tuple) => $tuple[1], $stack);
    }
}
