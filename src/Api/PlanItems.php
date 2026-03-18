<?php

namespace Ashraam\PennylaneLaravel\Api;

class PlanItems extends BaseApi
{
    /**
     * List all products
     *
     * @return array
     */
    public function list($page = 1, $per_page = 50)
    {
        return $this->requestJson('get', $this->getNamespace() . "plan_items?page={$page}&per_page={$per_page}");
    }


    /**
     * Create a new product
     *
     * @param array $data
     * @return array
     */
    public function create(array $data)
    {
        $payload = $this->buildPayload($data, 'plan_item');
        return $this->requestJson('post', $this->getNamespace() . "plan_items", [
            'json' => $payload,
        ]);
    }

}
