<?php

namespace Ashraam\PennylaneLaravel\Api;

class Suppliers extends BaseApiV1
{
    /**
     * List all suppliers
     *
     * @return array
     */
    public function list($page = 1)
    {
        $response = $this->client->request('get', self::API_NAMESPACE . "suppliers?page=$page");

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Create a new supplier
     *
     * @param array $data
     * @return array
     */
    public function create(array $data)
    {
        $response = $this->client->request('post', self::API_NAMESPACE . "suppliers", [
            'json' => [
                'supplier' => $data
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Retrieve a supplier by it's ID
     *
     * @param string $id
     * @return array
     */
    public function get(string $id)
    {
        $response = $this->client->request('get', self::API_NAMESPACE . "suppliers/{$id}");

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Update a supplier by it's ID
     *
     * @param string $id
     * @param array $data
     * @return array
     */
    public function update(string $id, array $data)
    {
        $response = $this->client->request('put', self::API_NAMESPACE . "suppliers/{$id}", [
            'json' => [
                'supplier' => $data
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
