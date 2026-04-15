<?php

namespace Ashraam\PennylaneLaravel\Tests\Api;

use Ashraam\PennylaneLaravel\Api\BankAccounts;
use Ashraam\PennylaneLaravel\Api\BaseApi;
use Ashraam\PennylaneLaravel\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

class BankAccountsTest extends TestCase
{
    public function testListUsesV2BankAccountsEndpointWithLimitAndSort(): void
    {
        [$api, $history] = $this->makeApi([
            new Response(200, [], json_encode(['items' => [], 'has_more' => false, 'next_cursor' => null])),
        ]);

        $api->list(1, 50, [], '-id', 'cursor_123');

        $request = $history[0]['request'];

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/v2/bank_accounts?limit=50&cursor=cursor_123&sort=-id', (string) $request->getUri());
    }

    public function testCreateSendsRawPayloadToV2Endpoint(): void
    {
        [$api, $history] = $this->makeApi([
            new Response(201, [], json_encode(['id' => 42])),
        ]);

        $payload = [
            'name' => 'Main account',
            'currency' => 'EUR',
        ];

        $api->create($payload);

        $request = $history[0]['request'];

        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/v2/bank_accounts', (string) $request->getUri());
        $this->assertSame(json_encode($payload), (string) $request->getBody());
    }

    public function testThrowsWhenUsingV1Namespace(): void
    {
        [$api] = $this->makeApi([], BaseApi::API_NAMESPACE_V1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Bank account endpoints are only available with the V2 API.');

        $api->list();
    }

    /**
     * @param array $responses
     * @param string $namespace
     * @return array{0: BankAccounts, 1: array}
     */
    private function makeApi(array $responses, string $namespace = BaseApi::API_NAMESPACE_V2): array
    {
        $history = [];
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($history));

        $client = new Client([
            'base_uri' => 'https://example.test/',
            'handler' => $handlerStack,
        ]);

        return [new BankAccounts($client, $namespace), $history];
    }
}
