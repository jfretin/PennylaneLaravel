<?php

namespace Ashraam\PennylaneLaravel;

use Ashraam\PennylaneLaravel\Api\BaseApi;
use GuzzleHttp\ClientInterface;
use Ashraam\PennylaneLaravel\Api\Enums;
use Ashraam\PennylaneLaravel\Api\Categories;
use Ashraam\PennylaneLaravel\Api\CustomerInvoices;
use Ashraam\PennylaneLaravel\Api\SupplierInvoices;
use Ashraam\PennylaneLaravel\Api\Products;
use Ashraam\PennylaneLaravel\Api\Customers;
use Ashraam\PennylaneLaravel\Api\Suppliers;
use Ashraam\PennylaneLaravel\Api\Estimates;
use Ashraam\PennylaneLaravel\Api\PlanItems;
use Ashraam\PennylaneLaravel\Api\LedgerEntries;
use Ashraam\PennylaneLaravel\Api\LedgerEntryLines;
use Ashraam\PennylaneLaravel\Api\LedgerAccounts;
use Ashraam\PennylaneLaravel\Api\Attachment;
use Ashraam\PennylaneLaravel\Api\Journals;

class PennylaneLaravel
{
    protected $client;
    protected $client_v2;

    const API_NAMESPACE = 'v1/';

    public function __construct(ClientInterface $client_v1, ClientInterface $client_v2)
    {
        $this->client = $client_v1;
        $this->client_v2 = $client_v2;
    }

    public function me()
    {
        $response = $this->client->request('get', self::API_NAMESPACE . 'me');

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Customers resource accessor with per-call version selection.
     * Usage: $api->customers('v2')->list(...)
     *
     * @param string|int|null $version 'v1'|'v2' or 1|2 (default v1)
     */
    public function customers($version = null)
    {
        [$client, $ns] = $this->resolveVersionAndClient($version);
        return new Customers($client, $ns);
    }

    /**
     * Suppliers resource accessor with per-call version selection.
     * Usage: $api->suppliers('v2')->list(...)
     *
     * @param string|int|null $version 'v1'|'v2' or 1|2 (default v1)
     */
    public function suppliers($version = null)
    {
        [$client, $ns] = $this->resolveVersionAndClient($version);
        return new Suppliers($client, $ns);
    }

    /**
     * Deprecated: use suppliers('v2').
     */
    public function suppliers2()
    {
        return new Suppliers($this->client_v2, BaseApi::API_NAMESPACE_V2);
    }

    /**
     * Products resource accessor with per-call version selection.
     */
    public function products($version = null)
    {
        [$client, $ns] = $this->resolveVersionAndClient($version);
        return new Products($client, $ns);
    }

    /**
     * Customer invoices accessor with per-call version selection.
     */
    public function customer_invoices($version = null)
    {
        [$client, $ns] = $this->resolveVersionAndClient($version);
        return new CustomerInvoices($client, $ns);
    }

    /**
     * Supplier invoices accessor with per-call version selection.
     */
    public function supplier_invoices($version = null)
    {
        [$client, $ns] = $this->resolveVersionAndClient($version);
        return new SupplierInvoices($client, $ns);
    }

    /**
     * Estimates accessor with per-call version selection.
     */
    public function estimates($version = null)
    {
        [$client, $ns] = $this->resolveVersionAndClient($version);
        return new Estimates($client, $ns);
    }

    /**
     * Enums accessor with per-call version selection.
     */
    public function enums($version = null)
    {
        [$client, $ns] = $this->resolveVersionAndClient($version);
        return new Enums($client, $ns);
    }

    /**
     * Categories accessor with per-call version selection.
     */
    public function categories($version = null)
    {
        [$client, $ns] = $this->resolveVersionAndClient($version);
        return new Categories($client, $ns);
    }

    /**
     * Plan items accessor with per-call version selection.
     */
    public function plan_items($version = null)
    {
        [$client, $ns] = $this->resolveVersionAndClient($version);
        return new PlanItems($client, $ns);
    }

    public function ledger_entries()
    {
        return new LedgerEntries($this->client_v2);
    }

    public function ledger_entry_lines()
    {
        return new LedgerEntryLines($this->client_v2);
    }

    public function ledger_accounts()
    {
        return new LedgerAccounts($this->client_v2);
    }

    public function attachments()
    {
        return new Attachment($this->client_v2);
    }

    public function journals()
    {
        return new Journals($this->client_v2);
    }

    /**
     * Normalize requested version and return [client, namespace].
     *
     * @param string|int|null $version
     * @return array
     */
    private function resolveVersionAndClient($version): array
    {
        $v = is_null($version) ? 'v1' : strtolower((string)$version);
        if ($v === '2' || $v === 'v2') {
            return [$this->client_v2, BaseApi::API_NAMESPACE_V2];
        }
        // Default to v1
        return [$this->client, BaseApi::API_NAMESPACE_V1];
    }
}
