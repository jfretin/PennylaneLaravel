<?php

namespace Ashraam\PennylaneLaravel\Api;

class SupplierInvoices extends BaseApiV1
{

    /**
     * List all invoices
     *
     * @param array $filters
     * @return array
     */
    public function list($page = 1, $per_page = 20, array $filters = [])
    {
        $response = $this->client->request('get', self::API_NAMESPACE . "supplier_invoices", [
            'query' => [
                'page' => $page,
                'per_page' => $per_page,
                'filter' => json_encode($filters)
            ]
        ]);
        $ret = json_decode($response->getBody()->getContents(), true);
        return $ret;
    }


    /**
     * Create a new invoice
     *
     * @param array $data
     * @param boolean $create_customer
     * @param boolean $create_products
     * @return array
     */
    public function create(array $data, bool $create_customer = false, bool $create_products = false)
    {
        $response = $this->client->request('post', self::API_NAMESPACE . "supplier_invoices", [
            'json' => [
                'create_customer' => $create_customer,
                'create_products' => $create_products,
                'invoice' => $data
            ]
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
        $response = $this->client->request('get', self::API_NAMESPACE . "supplier_invoices/{$id}");

        return json_decode($response->getBody()->getContents(), true);
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
        $response = $this->client->request('post', self::API_NAMESPACE . "supplier_invoices/import", [
            'json' => [
                'create_customer' => $create_customer,
                'file_url' => $file_url,
                'invoice' => $data
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
