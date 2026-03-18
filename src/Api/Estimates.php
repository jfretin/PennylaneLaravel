<?php

namespace Ashraam\PennylaneLaravel\Api;

class Estimates extends BaseApi
{
    /**
     * List all estimates
     *
     * @param array $filters
     * @return array
     */
    public function list(array $filters = [])
    {
        return $this->requestJson('get', $this->getNamespace() . "customer_estimates", [
            'query' => [
                'filter' => json_encode($filters)
            ]
        ]);
    }

    /**
     * Create a new estimate
     *
     * @param array $data
     * @param boolean $create_customer
     * @param boolean $create_products
     * @return array
     */
    public function create(array $data, bool $create_customer = false, bool $create_products = false)
    {
        $base = [
            'create_customer' => $create_customer,
            'create_products' => $create_products,
            'estimate' => $data,
        ];
        $payload = $this->buildPayload($base, 'estimate');
        return $this->requestJson('post', $this->getNamespace() . "customer_estimates", [
            'json' => $payload,
        ]);
    }


    /**
     * Retrieve an estimate by it's ID
     *
     * @param string $id
     * @return array
     */
    public function get(string $id)
    {
        return $this->requestJson('get', $this->getNamespace() . "customer_estimates/{$id}");
    }
}
