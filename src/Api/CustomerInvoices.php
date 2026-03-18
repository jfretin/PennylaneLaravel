<?php

namespace Ashraam\PennylaneLaravel\Api;

class CustomerInvoices extends BaseApi
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
}
