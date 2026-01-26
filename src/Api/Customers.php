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
     * - V1: page-based pagination (params: $page, $filters)
     * - V2: cursor-based (params: $cursor, $limit, $filters, $sort)
     *
     * @param int $page Ignored (kept for backward compatibility)
     * @param int $per_page V2 default 20 on API side
     * @param array $filters Array of filters (json-encoded)
     * @param string|null $sort
     * @param string|null $cursor
     */
    public function list($page = 1, $per_page = 25, $filters = [], ?string $sort = null, ?string $cursor = null)
    {
        $ns = $this->getNamespace();
        $query = [];

        if ($this->isV2()) {
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
            if (!empty($filters)) {
                $query['filter'] = is_string($filters) ? $filters : json_encode($filters);
            }
        }

        $query_string = http_build_query($query);
        $response = $this->client->request('get', $ns . 'customers' . ($query_string ? ('?' . $query_string) : ''));

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Create a new customer
     * - V2 only: POST /company_customers or /individual_customers
     */
    public function create(array $data)
    {
        $ns = $this->getNamespace();
        $payload = $this->buildPayload($data, 'customer');
        $customer_type = $this->resolveCustomerType($payload);
        $payload = $this->stripInternalKeys($payload);
        $endpoint = $customer_type === 'individual' ? 'individual_customers' : 'company_customers';

        $response = $this->client->request('post', $ns . $endpoint, [
            'json' => $payload,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Retrieve a customer by ID
     * - V2 only: $id is integer
     */
    public function get($id)
    {
        $ns = $this->getNamespace();
        $response = $this->client->request('get', $ns . "customers/{$id}");

        return json_decode($response->getBody()->getContents(), true);
    }


    /**
     * Update a customer by ID
     * - V2 only: PUT /company_customers/{id} or /individual_customers/{id}
     */
    public function update($id, array $data)
    {
        $ns = $this->getNamespace();
        $payload = $this->buildPayload($data, 'customer');
        $customer_type = $this->resolveCustomerType($payload);
        $payload = $this->stripInternalKeys($payload);
        $endpoint = $customer_type === 'individual' ? 'individual_customers' : 'company_customers';

        $response = $this->client->request('put', $ns . "{$endpoint}/{$id}", [
            'json' => $payload,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Resolve customer type for V2 endpoints.
     *
     * @param array $data
     * @return string
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    private function resolveCustomerType(array $data): string
    {
        $type = $data['customer_type'] ?? null;
        if (is_string($type) && $type !== '') {
            return strtolower($type) === 'individual' ? 'individual' : 'company';
        }

        if (array_key_exists('first_name', $data) || array_key_exists('last_name', $data)) {
            return 'individual';
        }

        return 'company';
    }

    /**
     * Remove internal keys not accepted by V2 payloads.
     *
     * @param array $data
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    private function stripInternalKeys(array $data): array
    {
        unset($data['customer_type']);
        return $data;
    }
}
