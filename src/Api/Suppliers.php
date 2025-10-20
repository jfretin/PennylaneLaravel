<?php

namespace Ashraam\PennylaneLaravel\Api;

class Suppliers extends BaseApi
{
    /**
     * List suppliers
     * - V1: page-based pagination (param: $page, $per_page)
     * - V2: cursor-based (params: $cursor, $limit, $filter, $sort)
     */
    public function list($page = 1, $per_page = 20, array $filters = [], ?string $sort = null, ?string $cursor = null)
    {
        $ns = $this->getNamespace();
        $query = [];

        if (strpos($ns, 'v2/') === 0) {
            $query['limit'] = $per_page;
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
        }

        $query_string = http_build_query($query);
        $url = $ns . 'suppliers' . ($query_string ? ('?' . $query_string) : '');
        $response = $this->client->request('get', $url);

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Create a supplier
     * - V1: payload enveloped as { supplier: {...} }
     * - V2: top-level payload {...}
     */
    public function create(array $data)
    {
        $ns = $this->getNamespace();
        $payload = $this->buildPayload($data, 'supplier');

        $response = $this->client->request('post', $ns . 'suppliers', [
            'json' => $payload,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Retrieve a supplier by ID
     * - V1: $id is source_id (string)
     * - V2: $id is integer
     */
    public function get($id)
    {
        $ns = $this->getNamespace();
        $response = $this->client->request('get', $ns . "suppliers/{$id}");

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Update a supplier
     * - V1: payload enveloped as { supplier: {...} }
     * - V2: top-level payload {...}
     */
    public function update($id, array $data)
    {
        $ns = $this->getNamespace();
        $payload = $this->buildPayload($data, 'supplier');

        $response = $this->client->request('put', $ns . "suppliers/{$id}", [
            'json' => $payload,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
