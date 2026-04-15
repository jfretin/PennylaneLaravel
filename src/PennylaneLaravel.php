<?php

namespace Ashraam\PennylaneLaravel;

use Ashraam\PennylaneLaravel\Api\BaseApi;
use GuzzleHttp\ClientInterface;
use Ashraam\PennylaneLaravel\Api\Enums;
use Ashraam\PennylaneLaravel\Api\Categories;
use Ashraam\PennylaneLaravel\Api\CustomerInvoices;
use Ashraam\PennylaneLaravel\Api\CustomerInvoiceTemplates;
use Ashraam\PennylaneLaravel\Api\SupplierInvoices;
use Ashraam\PennylaneLaravel\Api\BankAccounts;
use Ashraam\PennylaneLaravel\Api\Products;
use Ashraam\PennylaneLaravel\Api\Customers;
use Ashraam\PennylaneLaravel\Api\Suppliers;
use Ashraam\PennylaneLaravel\Api\Estimates;
use Ashraam\PennylaneLaravel\Api\PlanItems;
use Ashraam\PennylaneLaravel\Api\Transactions;
use Ashraam\PennylaneLaravel\Api\LedgerEntries;
use Ashraam\PennylaneLaravel\Api\LedgerEntryLines;
use Ashraam\PennylaneLaravel\Api\LedgerAccounts;
use Ashraam\PennylaneLaravel\Api\Attachment;
use Ashraam\PennylaneLaravel\Api\Journals;
use Ashraam\PennylaneLaravel\Api\Changelogs;

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
        return $this->configureResource(new Customers($client, $ns), 'customers');
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
        return $this->configureResource(new Suppliers($client, $ns), 'suppliers');
    }

    /**
     * Deprecated: use suppliers('v2').
     */
    public function suppliers2()
    {
        return $this->configureResource(new Suppliers($this->client_v2, BaseApi::API_NAMESPACE_V2), 'suppliers');
    }

    /**
     * Products resource accessor with per-call version selection.
     */
    public function products($version = null)
    {
        [$client, $ns] = $this->resolveVersionAndClient($version);
        return $this->configureResource(new Products($client, $ns), 'products');
    }

    /**
     * Customer invoices accessor with per-call version selection.
     */
    public function customer_invoices($version = null)
    {
        [$client, $ns] = $this->resolveVersionAndClient($version);
        return $this->configureResource(new CustomerInvoices($client, $ns), 'customer_invoices');
    }

    /**
     * Supplier invoices accessor with per-call version selection.
     */
    public function supplier_invoices($version = null)
    {
        [$client, $ns] = $this->resolveVersionAndClient($version);
        return $this->configureResource(new SupplierInvoices($client, $ns), 'supplier_invoices');
    }

    /**
     * Customer invoice templates accessor (V2 only).
     *
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function customer_invoice_templates()
    {
        return $this->configureResource(new CustomerInvoiceTemplates($this->client_v2), 'customer_invoice_templates');
    }

    /**
     * Estimates accessor with per-call version selection.
     */
    public function estimates($version = null)
    {
        [$client, $ns] = $this->resolveVersionAndClient($version);
        return $this->configureResource(new Estimates($client, $ns), 'estimates');
    }

    /**
     * Enums accessor with per-call version selection.
     */
    public function enums($version = null)
    {
        [$client, $ns] = $this->resolveVersionAndClient($version);
        return $this->configureResource(new Enums($client, $ns), 'enums');
    }

    /**
     * Categories accessor with per-call version selection.
     */
    public function categories($version = null)
    {
        [$client, $ns] = $this->resolveVersionAndClient($version);
        return $this->configureResource(new Categories($client, $ns), 'categories');
    }

    /**
     * Plan items accessor with per-call version selection.
     */
    public function plan_items($version = null)
    {
        [$client, $ns] = $this->resolveVersionAndClient($version);
        return $this->configureResource(new PlanItems($client, $ns), 'plan_items');
    }

    /**
     * Bank accounts accessor (V2 only).
     *
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function bank_accounts()
    {
        return $this->configureResource(new BankAccounts($this->client_v2, BaseApi::API_NAMESPACE_V2), 'bank_accounts');
    }

    /**
     * Transactions accessor (V2 only).
     *
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function transactions()
    {
        return $this->configureResource(new Transactions($this->client_v2, BaseApi::API_NAMESPACE_V2), 'transactions');
    }

    public function ledger_entries()
    {
        return $this->configureResource(new LedgerEntries($this->client_v2), 'ledger_entries');
    }

    public function ledger_entry_lines()
    {
        return $this->configureResource(new LedgerEntryLines($this->client_v2), 'ledger_entry_lines');
    }

    public function ledger_accounts()
    {
        return $this->configureResource(new LedgerAccounts($this->client_v2), 'ledger_accounts');
    }

    public function attachments()
    {
        return $this->configureResource(new Attachment($this->client_v2), 'attachments');
    }

    public function journals()
    {
        return $this->configureResource(new Journals($this->client_v2), 'journals');
    }

    public function changelogs()
    {
        return $this->configureResource(new Changelogs($this->client_v2, BaseApi::API_NAMESPACE_V2), 'changelogs');
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

    /**
     * Apply the configured 2026 behavior override for a resource, if any.
     *
     * @param BaseApi $resource
     * @param string $resourceKey
     * @return BaseApi
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    private function configureResource(BaseApi $resource, string $resourceKey): BaseApi
    {
        $override = $this->resolve2026ApiChangesOverride($resourceKey);
        if ($override === null) {
            return $resource;
        }

        return $resource->with2026ApiChanges($override);
    }

    /**
     * Resolve the resource-specific 2026 behavior override from configuration.
     *
     * @param string $resourceKey
     * @return bool|null
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    private function resolve2026ApiChangesOverride(string $resourceKey): ?bool
    {
        $configured = config('pennylane-laravel.use_2026_api_changes_overrides.' . $resourceKey);
        if ($configured === null || $configured === '') {
            return null;
        }

        if (is_bool($configured)) {
            return $configured;
        }

        return filter_var($configured, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
