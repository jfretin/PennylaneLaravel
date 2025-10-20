<?php

namespace Ashraam\PennylaneLaravel\Api;

class Customers extends BaseApi
{
    use Filterable;

    private $filter_fields = [
        'updated_at',
        'created_at',
    ];

    /**
     * List customers
     * - V1: page-based pagination (param: $page)
     * - V2: cursor-based (params: $cursor, $limit, $filters, $sort)
     *
     * @param int $page Only for V1
     * @param int $per_page For both V1/V2 (V2 default 20 on API side)
     * @param array $filters Array of filters. V1 uses Filterable string, V2 json-encoded array
     * @param string|null $sort Only for V2
     * @param string|null $cursor Only for V2
     */
    public function list($page = 1, $per_page = 25, $filters = [], ?string $sort = null, ?string $cursor = null)
    {
        $ns = $this->getNamespace();

        if (strpos($ns, 'v2/') === 0) {
            $query = ['limit' => $per_page];
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
            $filter = $this->get_filters($filters);
            $query = [
                'page'     => $page,
                'per_page' => $per_page,
            ];
            if ($filter !== '') {
                $query['filter'] = $filter;
            }
        }

        $query_string = http_build_query($query);
        $response = $this->client->request('get', $ns . 'customers' . ($query_string ? ('?' . $query_string) : ''));

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Create a new customer
     * - V1: payload enveloped as { customer: {...} }
     * - V2: top-level payload {...}
     */
    public function create(array $data)
    {
        $ns = $this->getNamespace();
        $payload = $this->buildPayload($data, 'customer');

        $response = $this->client->request('post', $ns . 'customers', [
            'json' => $payload,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Retrieve a customer by ID
     * - V1: $id is source_id (string)
     * - V2: $id is integer
     */
    public function get($id)
    {
        $ns = $this->getNamespace();
        $response = $this->client->request('get', $ns . "customers/{$id}");

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Update a customer by ID
     * - V1: payload enveloped as { customer: {...} }
     * - V2: top-level payload {...}
     */
    public function update($id, array $data)
    {
        $ns = $this->getNamespace();
        $payload = $this->buildPayload($data, 'customer');

        $response = $this->client->request('put', $ns . "customers/{$id}", [
            'json' => $payload,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
