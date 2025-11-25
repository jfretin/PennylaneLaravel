<?php

namespace Ashraam\PennylaneLaravel\Api;

class SupplierInvoices extends BaseApi
{

    /**
     * List all invoices
     *
     * @param array $filters
     * @return array
     */
    public function list($page = 1, $per_page = 20, array $filters = [], ?string $sort = null, ?string $cursor = null)
    {
        $ns = $this->getNamespace();
        $query = [];

        if ($this->isV2()) {
            if ($per_page !== null) {
                $query['limit'] = $per_page; // V2 often uses `limit`
            }
            if ($cursor !== null) {
                $query['cursor'] = $cursor;
            }
            if (!empty($filters)) {
                $query['filter'] = json_encode($filters);
            }
            if (!empty($sort)) {
                $query['sort'] = $sort;
            }
        } else {
            $query['page'] = $page;
            $query['per_page'] = $per_page;
            if (!empty($filters)) {
                $query['filter'] = json_encode($filters);
            }
        }

        $query_string = http_build_query($query);
        $response = $this->client->request('get', $ns . 'supplier_invoices' . ($query_string ? ('?' . $query_string) : ''));

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Create a new invoice
     *
     * @param array $data
     * @param boolean $create_supplier
     * @param boolean $create_products
     * @return array
     */
    public function create(array $data, bool $create_supplier = false, bool $create_products = false)
    {
        $base = [
            'create_supplier' => $create_supplier,
            /*'create_products' => $create_products,*/
            'invoice' => $data,
        ];
        $payload = $this->buildPayload($base, 'invoice');

        $response = $this->client->request('post', $this->getNamespace() . "supplier_invoices", [
            'json' => $payload
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Get an invoice by it's ID
     *
     * @param string $id
     * @return array
     */
    public function get(string $id)
    {
        $response = $this->client->request('get', $this->getNamespace() . "supplier_invoices/{$id}");

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Import an invoice
     *
     * @param array $data
     * @param string $file_url
     * @param boolean $create_supplier
     * @return array
     */
    public function import(array $data, bool $create_supplier = false, string $file = '')
    {
        $json = [
            'invoice' => $data
        ];
        $payload = $this->buildPayload($json, 'invoice');

        $response = $this->client->request('post', $this->getNamespace() . "supplier_invoices/import", [
            'json' => $payload
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Retrieve a supplier invoice sub-resource (invoice_lines, categories, payments, matched_transactions).
     *
     * @param int|string $invoiceId
     * @param string $resource
     * @param array $query
     * @return array
     */
    public function subResource(int|string $invoiceId, string $resource, array $query = []): array
    {
        if (!$this->isV2()) {
            throw new \RuntimeException('Supplier invoice sub-resources are only available with the V2 API.');
        }

        $allowed = [
            'invoice_lines',
            'categories',
            'payments',
            'matched_transactions',
        ];

        if (!in_array($resource, $allowed, true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported supplier invoice sub-resource "%s".', $resource));
        }

        $queryString = http_build_query($query);
        $endpoint = sprintf(
            '%ssupplier_invoices/%s/%s%s',
            $this->getNamespace(),
            $invoiceId,
            $resource,
            $queryString ? ('?' . $queryString) : ''
        );

        $response = $this->client->request('get', $endpoint);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Replace categories of a supplier invoice (V2 only).
     *
     * @param int|string $invoiceId
     * @param array $categories
     * @return array
     */
    public function setCategories(int|string $invoiceId, array $categories): array
    {
        if (!$this->isV2()) {
            throw new \RuntimeException('Supplier invoice categories update is only available with the V2 API.');
        }

        $endpoint = sprintf('%ssupplier_invoices/%s/categories', $this->getNamespace(), $invoiceId);

        $response = $this->client->request('put', $endpoint, [
            'json' => $categories,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

}
