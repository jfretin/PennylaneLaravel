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
    public function testListUsesV2TransactionsEndpointWithCursorFilterAndSort(): void
    {
        [$api, $history] = $this->makeApi([
            new Response(200, [], json_encode(['items' => [], 'has_more' => false, 'next_cursor' => null])),
        ]);

        $api->list(1, 100, [
            ['field' => 'bank_account_id', 'operator' => 'eq', 'value' => '42'],
            ['field' => 'ignored', 'operator' => 'eq', 'value' => 'nope'],
        ], '-id', 'cursor_abc');

        $request = $history[0]['request'];

        $this->assertSame('GET', $request->getMethod());
        $this->assertStringStartsWith('/v2/transactions?', (string) $request->getUri());
        $this->assertStringContainsString('limit=100', (string) $request->getUri());
        $this->assertStringContainsString('cursor=cursor_abc', (string) $request->getUri());
        $this->assertStringContainsString('sort=-id', (string) $request->getUri());
        $this->assertStringContainsString(
            rawurlencode('[{"field":"bank_account_id","operator":"eq","value":"42"}]'),
            (string) $request->getUri()
        );
    }

    public function testUpdateSendsRawPayloadToTransactionEndpoint(): void
    {
        [$api, $history] = $this->makeApi([
            new Response(200, [], json_encode(['id' => 42])),
        ]);

        $payload = ['supplier_id' => 84];

        $api->update(42, $payload);

        $request = $history[0]['request'];

        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/v2/transactions/42', (string) $request->getUri());
        $this->assertSame(json_encode($payload), (string) $request->getBody());
    }

    public function testSetCategoriesUsesTransactionCategoriesEndpoint(): void
    {
        [$api, $history] = $this->makeApi([
            new Response(200, [], json_encode(['items' => []])),
        ]);

        $payload = [
            ['id' => 59, 'weight' => '0.5'],
            ['id' => 33, 'weight' => '0.5'],
        ];

        $api->setCategories(42, $payload);

        $request = $history[0]['request'];

        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/v2/transactions/42/categories', (string) $request->getUri());
        $this->assertSame(json_encode($payload), (string) $request->getBody());
    }

    public function testMatchedInvoicesAndCategoriesSupportCursorPagination(): void
    {
        [$api, $history] = $this->makeApi([
            new Response(200, [], json_encode(['items' => [], 'has_more' => false, 'next_cursor' => null])),
            new Response(200, [], json_encode(['items' => [], 'has_more' => false, 'next_cursor' => null])),
        ]);

        $api->matchedInvoices(42, ['limit' => 10, 'cursor' => 'next_invoices']);
        $api->categories(42, ['limit' => 15, 'cursor' => 'next_categories']);

        $this->assertSame('/v2/transactions/42/matched_invoices?cursor=next_invoices&limit=10', (string) $history[0]['request']->getUri());
        $this->assertSame('/v2/transactions/42/categories?cursor=next_categories&limit=15', (string) $history[1]['request']->getUri());
    }

    public function testThrowsWhenUsingV1Namespace(): void
    {
        [$api] = $this->makeApi([], BaseApi::API_NAMESPACE_V1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transaction endpoints are only available with the V2 API.');

        $api->get(42);
    }

    /**
     * @param array $responses
     * @param string $namespace
     * @return array{0: Transactions, 1: array}
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

        return [new Transactions($client, $namespace), $history];
    }
}
