<?php

namespace Ashraam\PennylaneLaravel\Tests\Api;

use Ashraam\PennylaneLaravel\Api\BaseApi;
use Ashraam\PennylaneLaravel\Api\CustomerInvoices;
use Ashraam\PennylaneLaravel\Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

class CustomerInvoicesTest extends TestCase
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $history = [];

    public function testSubResourceUsesCustomerInvoiceEndpointWithQuery(): void
    {
        $api = $this->makeApi([
            new Response(200, [], json_encode(['items' => []])),
        ]);

        $api->subResource(42, 'invoice_lines', [
            'cursor' => 'next_lines',
            'limit' => 50,
            'sort' => '-id',
        ]);

        $request = $this->history[0]['request'];

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/v2/customer_invoices/42/invoice_lines', $request->getUri()->getPath());
        $this->assertSame('cursor=next_lines&limit=50&sort=-id', $request->getUri()->getQuery());
    }

    public function testNamedHelpersUseExpectedSubResourceEndpoints(): void
    {
        $api = $this->makeApi([
            new Response(200, [], json_encode(['items' => []])),
            new Response(200, [], json_encode(['items' => []])),
            new Response(200, [], json_encode(['items' => []])),
            new Response(200, [], json_encode(['items' => []])),
            new Response(200, [], json_encode(['items' => []])),
            new Response(200, [], json_encode(['items' => []])),
            new Response(200, [], json_encode(['items' => []])),
        ]);

        $api->invoiceLineSections(42);
        $api->lines(42);
        $api->customHeaderFields(42);
        $api->categories(42);
        $api->payments(42);
        $api->transactions(42);
        $api->appendices(42);

        $paths = array_map(
            static fn (array $entry): string => $entry['request']->getUri()->getPath(),
            $this->history
        );

        $this->assertSame([
            '/v2/customer_invoices/42/invoice_line_sections',
            '/v2/customer_invoices/42/invoice_lines',
            '/v2/customer_invoices/42/custom_header_fields',
            '/v2/customer_invoices/42/categories',
            '/v2/customer_invoices/42/payments',
            '/v2/customer_invoices/42/matched_transactions',
            '/v2/customer_invoices/42/appendices',
        ], $paths);
    }

    public function testCustomerFollowsLinkedCustomerUrl(): void
    {
        $api = $this->makeApi([
            new Response(200, [], json_encode([
                'id' => 42,
                'customer' => [
                    'id' => 84,
                    'url' => 'https://app.pennylane.com/api/external/v2/customers/84',
                ],
            ])),
            new Response(200, [], json_encode(['id' => 84])),
        ]);

        $customer = $api->customer(42);

        $this->assertSame(['id' => 84], $customer);
        $this->assertSame('/v2/customer_invoices/42', $this->history[0]['request']->getUri()->getPath());
        $this->assertSame('/v2/customers/84', $this->history[1]['request']->getUri()->getPath());
    }

    public function testCustomerFallsBackToLinkedCustomerId(): void
    {
        $api = $this->makeApi([
            new Response(200, [], json_encode([
                'id' => 42,
                'customer' => [
                    'id' => 84,
                ],
            ])),
            new Response(200, [], json_encode(['id' => 84])),
        ]);

        $api->customer(42);

        $this->assertSame('/v2/customers/84', $this->history[1]['request']->getUri()->getPath());
    }

    public function testRejectsUnsupportedSubResource(): void
    {
        $api = $this->makeApi([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported customer invoice sub-resource "customer".');

        $api->subResource(42, 'customer');
    }

    public function testThrowsWhenUsingV1NamespaceForSubResources(): void
    {
        $api = $this->makeApi([], BaseApi::API_NAMESPACE_V1);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Customer invoice sub-resources are only available with the V2 API.');

        $api->lines(42);
    }

    /**
     * @param array $responses
     * @param string $namespace
     * @return CustomerInvoices
     */
    private function makeApi(array $responses, string $namespace = BaseApi::API_NAMESPACE_V2): CustomerInvoices
    {
        $this->history = [];
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($this->history));

        $client = new Client([
            'base_uri' => 'https://example.test/',
            'handler' => $handlerStack,
        ]);

        return new CustomerInvoices($client, $namespace);
    }
}
