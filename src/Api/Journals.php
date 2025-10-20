<?php

namespace Ashraam\PennylaneLaravel\Api;

class Journals extends BaseApi
{
    protected $defaultNamespace = self::API_NAMESPACE_V2;
    /**
     * List all entries
     *
     * @return array
     */
    public function list($page = 1, $per_page = 20)
    {
        $response = $this->client->request('get', $this->getNamespace() . "journals?page=$page&per_page=$per_page");

        return json_decode($response->getBody()->getContents(), true);
    }
}
