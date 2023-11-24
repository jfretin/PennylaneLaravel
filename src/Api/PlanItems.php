<?php

namespace Ashraam\PennylaneLaravel\Api;

class PlanItems extends BaseApiV1
{
    /**
     * List all products
     *
     * @return array
     */
    public function list($page = 1, $per_page = 50)
    {
        $response = $this->client->request('get', self::API_NAMESPACE . "plan_items?page={$page}&per_page={$per_page}");

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Create a new product
     *
     * @param array $data
     * @return array
     */
    public function create(array $data)
    {
        $response = $this->client->request('post', self::API_NAMESPACE . "plan_items", [
            'json' => [
                'plan_item' => $data
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

}
