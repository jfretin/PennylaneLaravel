<?php

namespace Ashraam\PennylaneLaravel\Api;

class Customers extends BaseApiV1
{
    /**
     * List all customers
     *
     * @return array
     */
    public function list()
    {
        $response = $this->client->request('get', self::API_NAMESPACE . "customers");

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Create a new customer
     *
     * @param array $data
     * @return array
     */
    public function create(array $data)
    {
        $response = $this->client->request('post', self::API_NAMESPACE . "customers", [
            'json' => [
                'customer' => $data
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Retrieve a customer by it's ID
     *
     * @param string $id
     * @return array
     */
    public function get(string $id)
    {
        $response = $this->client->request('get', self::API_NAMESPACE . "customers/{$id}");

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Update a customer by it's ID
     *
     * @param string $id
     * @param array $data
     * @return array
     */
    public function update(string $id, array $data)
    {
        $response = $this->client->request('put', self::API_NAMESPACE . "customers/{$id}", [
            'json' => [
                'customer' => $data
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
