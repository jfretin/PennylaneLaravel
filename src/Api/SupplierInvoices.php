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
            'create_supplier' => $create_supplier,
            'invoice' => $data
        ];
        if ($file != '') {
            if (0 === stripos($file, 'http')) {
                $json['file_url'] = $file;
            } else {
                $json['file'] = $file;
            }
        }
        
        $payload = $this->buildPayload($json, 'invoice');

        $response = $this->client->request('post', $this->getNamespace() . "supplier_invoices/import", [
            'json' => $payload
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
