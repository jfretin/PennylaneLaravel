<?php

namespace Ashraam\PennylaneLaravel\Api;

class CustomerInvoices extends BaseApi
{
    private const V2_SUB_RESOURCES = [
        'invoice_line_sections',
        'invoice_lines',
        'custom_header_fields',
        'categories',
        'payments',
        'matched_transactions',
        'appendices',
    ];

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
                $query['limit'] = $per_page; // V2 uses `limit`
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
        return $this->requestJson('get', $ns . 'customer_invoices' . ($query_string ? ('?' . $query_string) : ''));
    }


    /**
     * Create a new invoice
     *
     * @param array $data
     * @return array
     */
    public function create(array $data)
    {
        $payload = $this->buildPayload($data, 'invoice');
        return $this->requestJson('post', $this->getNamespace() . "customer_invoices", [
            'json' => $payload
        ]);
    }


    /**
     * Get an invoice by it's ID
     *
     * @param string $id
     * @return array
     */
    public function get(string $id)
    {
        return $this->requestJson('get', $this->getNamespace() . "customer_invoices/{$id}");
    }

    /**
     * Retrieve a customer invoice sub-resource.
     *
     * @param int|string $invoiceId
     * @param string $resource
     * @param array $query
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function subResource(int|string $invoiceId, string $resource, array $query = []): array
    {
        if (!$this->isV2()) {
            throw new \RuntimeException('Customer invoice sub-resources are only available with the V2 API.');
        }

        if (!in_array($resource, self::V2_SUB_RESOURCES, true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported customer invoice sub-resource "%s".', $resource));
        }

        $queryString = http_build_query($query);
        $endpoint = sprintf(
            '%scustomer_invoices/%s/%s%s',
            $this->getNamespace(),
            $invoiceId,
            $resource,
            $queryString ? ('?' . $queryString) : ''
        );

        return $this->requestJson('get', $endpoint);
    }

    /**
     * List invoice line sections for a customer invoice.
     *
     * @param int|string $invoiceId
     * @param array $query
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function invoiceLineSections(int|string $invoiceId, array $query = []): array
    {
        return $this->subResource($invoiceId, 'invoice_line_sections', $query);
    }

    /**
     * List invoice lines for a customer invoice.
     *
     * @param int|string $invoiceId
     * @param array $query
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function invoiceLines(int|string $invoiceId, array $query = []): array
    {
        return $this->subResource($invoiceId, 'invoice_lines', $query);
    }

    /**
     * Alias for invoiceLines().
     *
     * @param int|string $invoiceId
     * @param array $query
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function lines(int|string $invoiceId, array $query = []): array
    {
        return $this->invoiceLines($invoiceId, $query);
    }

    /**
     * List custom header fields for a customer invoice.
     *
     * @param int|string $invoiceId
     * @param array $query
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function customHeaderFields(int|string $invoiceId, array $query = []): array
    {
        return $this->subResource($invoiceId, 'custom_header_fields', $query);
    }

    /**
     * List categories for a customer invoice.
     *
     * @param int|string $invoiceId
     * @param array $query
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function categories(int|string $invoiceId, array $query = []): array
    {
        return $this->subResource($invoiceId, 'categories', $query);
    }

    /**
     * List payments for a customer invoice.
     *
     * @param int|string $invoiceId
     * @param array $query
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function payments(int|string $invoiceId, array $query = []): array
    {
        return $this->subResource($invoiceId, 'payments', $query);
    }

    /**
     * List matched transactions for a customer invoice.
     *
     * @param int|string $invoiceId
     * @param array $query
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function matchedTransactions(int|string $invoiceId, array $query = []): array
    {
        return $this->subResource($invoiceId, 'matched_transactions', $query);
    }

    /**
     * Alias for matchedTransactions().
     *
     * @param int|string $invoiceId
     * @param array $query
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function transactions(int|string $invoiceId, array $query = []): array
    {
        return $this->matchedTransactions($invoiceId, $query);
    }

    /**
     * List appendices for a customer invoice.
     *
     * @param int|string $invoiceId
     * @param array $query
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function appendices(int|string $invoiceId, array $query = []): array
    {
        return $this->subResource($invoiceId, 'appendices', $query);
    }

    /**
     * Retrieve the linked customer for a customer invoice.
     *
     * @param int|string $invoiceId
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function customer(int|string $invoiceId): array
    {
        if (!$this->isV2()) {
            throw new \RuntimeException('Customer invoice linked customer retrieval is only available with the V2 API.');
        }

        $invoice = $this->get((string) $invoiceId);
        $customer = $invoice['customer'] ?? null;

        if (!is_array($customer)) {
            throw new \RuntimeException(sprintf('Customer invoice "%s" does not expose a linked customer.', $invoiceId));
        }

        if (!empty($customer['url']) && is_string($customer['url'])) {
            return $this->requestJson('get', $this->normalizeLinkedResourceUri($customer['url']));
        }

        if (!empty($customer['id'])) {
            return $this->requestJson('get', sprintf('%scustomers/%s', $this->getNamespace(), $customer['id']));
        }

        throw new \RuntimeException(sprintf('Customer invoice "%s" linked customer is missing both id and url.', $invoiceId));
    }

    /**
     * Mark a customer invoice as paid using the V2 API.
     *
     * @param int|string $customerInvoiceId
     * @return bool
     * @throws \RuntimeException When the API namespace is not V2
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function markAsPaid(int|string $customerInvoiceId): bool
    {
        if ($customerInvoiceId === '' || $customerInvoiceId === null) {
            throw new \InvalidArgumentException('Customer invoice id is required.');
        }

        if (!$this->isV2()) {
            throw new \RuntimeException('Customer invoice mark as paid is only available with the V2 API.');
        }

        $response = $this->request(
            'put',
            sprintf('%scustomer_invoices/%s/mark_as_paid', $this->getNamespace(), $customerInvoiceId)
        );

        if ($response->getStatusCode() !== 204) {
            throw new \RuntimeException(sprintf(
                'Unexpected response status when marking customer invoice as paid: %s.',
                $response->getStatusCode()
            ));
        }

        return true;
    }


    /**
     * Import an invoice
     *
     * @param array $data
     * @param string $file_url
     * @param boolean $create_customer
     * @return array
     */
    public function import(array $data, string $file_url, bool $create_customer)
    {
        $base = [
            'create_customer' => $create_customer,
            'file_url' => $file_url,
            'invoice' => $data,
        ];
        $payload = $this->buildPayload($base, 'invoice');
        return $this->requestJson('post', $this->getNamespace() . "customer_invoices/import", [
            'json' => $payload
        ]);
    }

    /**
     * Delete a draft customer invoice or draft credit note using the V2 API.
     *
     * @param int|string $customerInvoiceId
     * @return bool
     * @throws \RuntimeException When the API namespace is not V2
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function delete(int|string $customerInvoiceId): bool
    {
        if ($customerInvoiceId === '' || $customerInvoiceId === null) {
            throw new \InvalidArgumentException('Customer invoice id is required.');
        }

        if (!$this->isV2()) {
            throw new \RuntimeException('Customer invoice deletion is only available with the V2 API.');
        }

        $endpoint = sprintf('%scustomer_invoices/%s', $this->getNamespace(), $customerInvoiceId);
        $response = $this->request('delete', $endpoint);
        $statusCode = $response->getStatusCode();

        if ($statusCode !== 204) {
            throw new \RuntimeException(sprintf(
                'Unexpected response status when deleting customer invoice draft: %s.',
                $statusCode
            ));
        }

        return true;
    }

    /**
     * Normalize an absolute Pennylane API URL to the client-relative URI used by this package.
     *
     * @param string $url
     * @return string
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    private function normalizeLinkedResourceUri(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (is_string($path) && preg_match('#/api/external/(v[12]/.+)$#', $path, $matches)) {
            return $matches[1];
        }

        return $url;
    }
}
