<?php

namespace Ashraam\PennylaneLaravel\Tests\Api;

use Ashraam\PennylaneLaravel\Api\BaseApi;
use Ashraam\PennylaneLaravel\Api\Transactions;
use Ashraam\PennylaneLaravel\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

class TransactionsTest extends TestCase
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $history = [];

    public function testListUsesV2TransactionsEndpointWithCursorFilterAndSort(): void
    {
        $api = $this->makeApi([
            new Response(200, [], json_encode(['items' => [], 'has_more' => false, 'next_cursor' => null])),
        ]);

        $api->list(1, 100, [
            ['field' => 'bank_account_id', 'operator' => 'eq', 'value' => '42'],
            ['field' => 'ignored', 'operator' => 'eq', 'value' => 'nope'],
        ], '-id', 'cursor_abc');

        $request = $this->history[0]['request'];
        $uri = $request->getUri();

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/v2/transactions', $uri->getPath());
        $this->assertStringContainsString('limit=100', $uri->getQuery());
        $this->assertStringContainsString('cursor=cursor_abc', $uri->getQuery());
        $this->assertStringContainsString('sort=-id', $uri->getQuery());
        $this->assertStringContainsString(
            rawurlencode('[{"field":"bank_account_id","operator":"eq","value":"42"}]'),
            $uri->getQuery()
        );
    }

    public function testUpdateSendsRawPayloadToTransactionEndpoint(): void
    {
        $api = $this->makeApi([
            new Response(200, [], json_encode(['id' => 42])),
        ]);

        $payload = ['supplier_id' => 84];

        $api->update(42, $payload);

        $request = $this->history[0]['request'];

        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/v2/transactions/42', $request->getUri()->getPath());
        $this->assertSame(json_encode($payload), (string) $request->getBody());
    }

    public function testSetCategoriesUsesTransactionCategoriesEndpoint(): void
    {
        $api = $this->makeApi([
            new Response(200, [], json_encode(['items' => []])),
        ]);

        $payload = [
            ['id' => 59, 'weight' => '0.5'],
            ['id' => 33, 'weight' => '0.5'],
        ];

        $api->setCategories(42, $payload);

        $request = $this->history[0]['request'];

        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/v2/transactions/42/categories', $request->getUri()->getPath());
        $this->assertSame(json_encode($payload), (string) $request->getBody());
    }

    public function testMatchedInvoicesAndCategoriesSupportCursorPagination(): void
    {
        $api = $this->makeApi([
            new Response(200, [], json_encode(['items' => [], 'has_more' => false, 'next_cursor' => null])),
            new Response(200, [], json_encode(['items' => [], 'has_more' => false, 'next_cursor' => null])),
        ]);

        $api->matchedInvoices(42, ['limit' => 10, 'cursor' => 'next_invoices']);
        $api->categories(42, ['limit' => 15, 'cursor' => 'next_categories']);

        $this->assertSame('/v2/transactions/42/matched_invoices', $this->history[0]['request']->getUri()->getPath());
        $this->assertSame('cursor=next_invoices&limit=10', $this->history[0]['request']->getUri()->getQuery());
        $this->assertSame('/v2/transactions/42/categories', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame('cursor=next_categories&limit=15', $this->history[1]['request']->getUri()->getQuery());
    }

    public function testThrowsWhenUsingV1Namespace(): void
    {
        $api = $this->makeApi([], BaseApi::API_NAMESPACE_V1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transaction endpoints are only available with the V2 API.');

        $api->get(42);
    }

    /**
     * @param array $responses
     * @param string $namespace
     * @return Transactions
     */
    private function makeApi(array $responses, string $namespace = BaseApi::API_NAMESPACE_V2): Transactions
    {
        $this->history = [];
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($this->history));

        $client = new Client([
            'base_uri' => 'https://example.test/',
            'handler' => $handlerStack,
        ]);

        return new Transactions($client, $namespace);
    }
}
