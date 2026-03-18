<?php

namespace Ashraam\PennylaneLaravel\Api;

class Products extends BaseApi
{
    /**
     * List all products
     *
     * @return array
     */
    public function list()
    {
        return $this->requestJson('get', $this->getNamespace() . "products");
    }


    /**
     * Create a new product
     *
     * @param array $data
     * @return array
     */
    public function create(array $data)
    {
        $payload = $this->buildPayload($data, 'product');
        return $this->requestJson('post', $this->getNamespace() . "products", [
            'json' => $payload,
        ]);
    }


    /**
     * Retrieve a product by it's ID
     *
     * @param string $id
     * @return array
     */
    public function get(string $id)
    {
        return $this->requestJson('get', $this->getNamespace() . "products/{$id}");
    }


    /**
     * Update a product by it's ID
     *
     * @param string $id
     * @param array $data
     * @return array
     */
    public function update(string $id, array $data)
    {
        $payload = $this->buildPayload($data, 'product');
        return $this->requestJson('put', $this->getNamespace() . "products/{$id}", [
            'json' => $payload,
        ]);
    }
}
