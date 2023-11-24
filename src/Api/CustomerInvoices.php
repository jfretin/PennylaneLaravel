<?php

namespace Ashraam\PennylaneLaravel\Api;

class CustomerInvoices extends BaseApiV1
{

    /**
     * List all invoices
     *
     * @param array $filters
     * @return array
     */
    public function list(array $filters = [])
    {
        $response = $this->client->request('get', self::API_NAMESPACE . "customer_invoices", [
            'query' => [
                'filter' => json_encode($filters)
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
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
        $response = $this->client->request('post', "customer_invoices", [
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
        $response = $this->client->request('get', "customer_invoices/{$id}");

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
        $response = $this->client->request('post', "customer_invoices/import", [
            'json' => [
                'create_customer' => $create_customer,
                'file_url' => $file_url,
                'invoice' => $data
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
