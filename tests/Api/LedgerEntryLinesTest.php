<?php

namespace Ashraam\PennylaneLaravel\Tests\Api;

use Ashraam\PennylaneLaravel\Api\BaseApi;
use Ashraam\PennylaneLaravel\Api\LedgerEntryLines;
use Ashraam\PennylaneLaravel\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

class LedgerEntryLinesTest extends TestCase
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $history = [];

    public function testListAllUsesGlobalV2LedgerEntryLinesEndpoint(): void
    {
        $api = $this->makeApi([
            new Response(200, [], json_encode(['items' => [], 'has_more' => false, 'next_cursor' => null])),
        ]);

        $api->listAll(1, 100, [
            ['field' => 'journal_id', 'operator' => 'eq', 'value' => '42'],
            ['field' => 'date', 'operator' => 'gteq', 'value' => '2026-01-01'],
            ['field' => 'ignored', 'operator' => 'eq', 'value' => 'nope'],
        ], '-date', 'cursor_abc');

        $request = $this->history[0]['request'];
        $uri = $request->getUri();

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/v2/ledger_entry_lines', $uri->getPath());
        $this->assertStringContainsString('limit=100', $uri->getQuery());
        $this->assertStringContainsString('cursor=cursor_abc', $uri->getQuery());
        $this->assertStringContainsString('sort=-date', $uri->getQuery());
        $this->assertStringContainsString(
            rawurlencode('[{"field":"journal_id","operator":"eq","value":"42"},{"field":"date","operator":"gteq","value":"2026-01-01"}]'),
            $uri->getQuery()
        );
    }

    public function testListAllThrowsWhenUsingV1Namespace(): void
    {
        $api = $this->makeApi([], BaseApi::API_NAMESPACE_V1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Ledger entry line listing is only available with the V2 API.');

        $api->listAll();
    }

    /**
     * @param array $responses
     * @param string $namespace
     * @return LedgerEntryLines
     */
    private function makeApi(array $responses, string $namespace = BaseApi::API_NAMESPACE_V2): LedgerEntryLines
    {
        $this->history = [];
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($this->history));

        $client = new Client([
            'base_uri' => 'https://example.test/',
            'handler' => $handlerStack,
        ]);

        return new LedgerEntryLines($client, $namespace);
    }
}
