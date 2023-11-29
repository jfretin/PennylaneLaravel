<?php

namespace Ashraam\PennylaneLaravel;

use GuzzleHttp\ClientInterface;
use Ashraam\PennylaneLaravel\Api\Enums;
use Ashraam\PennylaneLaravel\Api\CustomerInvoices;
use Ashraam\PennylaneLaravel\Api\SupplierInvoices;
use Ashraam\PennylaneLaravel\Api\Products;
use Ashraam\PennylaneLaravel\Api\Customers;
use Ashraam\PennylaneLaravel\Api\Suppliers;
use Ashraam\PennylaneLaravel\Api\Estimates;
use Ashraam\PennylaneLaravel\Api\PlanItems;
use Ashraam\PennylaneLaravel\Api\LedgerEntries;
use Ashraam\PennylaneLaravel\Api\LedgerAccounts;
use Ashraam\PennylaneLaravel\Api\LedgerAttachments;
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

    public function customers()
    {
        return new Customers($this->client);
    }

    public function suppliers()
    {
        return new Suppliers($this->client);
    }

    public function products()
    {
        return new Products($this->client);
    }

    public function customer_invoices()
    {
        return new CustomerInvoices($this->client);
    }

    public function supplier_invoices()
    {
        return new SupplierInvoices($this->client);
    }

    public function estimates()
    {
        return new Estimates($this->client);
    }

    public function enums()
    {
        return new Enums($this->client);
    }

    public function plan_items()
    {
        return new PlanItems($this->client);
    }

    public function ledger_entries()
    {
        return new LedgerEntries($this->client_v2);
    }

    public function ledger_accounts()
    {
        return new LedgerAccounts($this->client_v2);
    }

    public function ledger_attachments()
    {
        return new LedgerAttachments($this->client_v2);
    }

    public function journals()
    {
        return new Journals($this->client_v2);
    }
}
