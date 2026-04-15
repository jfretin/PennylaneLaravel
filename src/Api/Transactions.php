<?php

namespace Ashraam\PennylaneLaravel\Api;

class Transactions extends BaseApi
{
    private array $filterFields = [
        'id',
        'bank_account_id',
        'journal_id',
        'date',
    ];

    private array $sortFields = [
        'id',
    ];

    /**
     * List transactions.
     *
     * @param int $page Ignored for V2-only resource, kept for signature consistency.
     * @param int $per_page Number of items requested through the `limit` query parameter.
     * @param array $filters Array of filter descriptors.
     * @param string|null $sort Sort field, optionally prefixed with `-`.
     * @param string|null $cursor Cursor for V2 pagination.
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function list($page = 1, $per_page = 20, array $filters = [], ?string $sort = null, ?string $cursor = null): array
    {
        $this->assertV2Only();

        $query = [
            'limit' => $per_page,
        ];

        if ($cursor !== null) {
            $query['cursor'] = $cursor;
        }

        $normalizedFilter = $this->normalizeFilters($filters);
        if ($normalizedFilter !== '') {
            $query['filter'] = $normalizedFilter;
        }

        $normalizedSort = $this->normalizeSort($sort);
        if ($normalizedSort !== '') {
            $query['sort'] = $normalizedSort;
        }

        $queryString = http_build_query($query);

        return $this->requestJson('get', $this->getNamespace() . 'transactions' . ($queryString ? ('?' . $queryString) : ''));
    }

    /**
     * Retrieve a transaction by ID.
     *
     * @param int|string $id
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function get(int|string $id): array
    {
        $this->assertV2Only();

        return $this->requestJson('get', $this->getNamespace() . "transactions/{$id}");
    }

    /**
     * Create a transaction.
     *
     * @param array $data
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function create(array $data): array
    {
        $this->assertV2Only();

        return $this->requestJson('post', $this->getNamespace() . 'transactions', [
            'json' => $data,
        ]);
    }

    /**
     * Update a transaction.
     *
     * @param int|string $id
     * @param array $data
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function update(int|string $id, array $data): array
    {
        $this->assertV2Only();

        return $this->requestJson('put', $this->getNamespace() . "transactions/{$id}", [
            'json' => $data,
        ]);
    }

    /**
     * List invoices matched to a transaction.
     *
     * @param int|string $transactionId
     * @param array $query
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function matchedInvoices(int|string $transactionId, array $query = []): array
    {
        return $this->subResource($transactionId, 'matched_invoices', $this->normalizeCursorPaginationQuery($query));
    }

    /**
     * List categories of a transaction.
     *
     * @param int|string $transactionId
     * @param array $query
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function categories(int|string $transactionId, array $query = []): array
    {
        return $this->subResource($transactionId, 'categories', $this->normalizeCursorPaginationQuery($query));
    }

    /**
     * Replace categories of a transaction.
     *
     * @param int|string $transactionId
     * @param array $categories
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function setCategories(int|string $transactionId, array $categories): array
    {
        $this->assertV2Only();

        return $this->requestJson('put', $this->getNamespace() . "transactions/{$transactionId}/categories", [
            'json' => $categories,
        ]);
    }

    /**
     * Retrieve a transaction sub-resource.
     *
     * @param int|string $transactionId
     * @param string $resource
     * @param array $query
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    private function subResource(int|string $transactionId, string $resource, array $query = []): array
    {
        $this->assertV2Only();

        $queryString = http_build_query($query);

        return $this->requestJson(
            'get',
            $this->getNamespace() . "transactions/{$transactionId}/{$resource}" . ($queryString ? ('?' . $queryString) : '')
        );
    }

    /**
     * Ensure the resource is used with the V2 namespace only.
     *
     * @return void
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    private function assertV2Only(): void
    {
        if (!$this->isV2()) {
            throw new \RuntimeException('Transaction endpoints are only available with the V2 API.');
        }
    }

    /**
     * Normalize supported transaction filters.
     *
     * @param array $filters
     * @return string
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    private function normalizeFilters(array $filters): string
    {
        $normalized = [];

        foreach ($filters as $filter) {
            if (!is_array($filter)) {
                continue;
            }

            $field = $filter['field'] ?? null;
            if (!is_string($field) || !in_array($field, $this->filterFields, true)) {
                continue;
            }

            $normalized[] = [
                'field' => $field,
                'operator' => $filter['operator'] ?? null,
                'value' => $filter['value'] ?? null,
            ];
        }

        return !empty($normalized) ? json_encode($normalized) : '';
    }

    /**
     * Normalize sort values against the API-allowed field list.
     *
     * @param string|null $sort
     * @return string
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    private function normalizeSort(?string $sort): string
    {
        if ($sort === null || $sort === '') {
            return '';
        }

        $sortField = ltrim($sort, '-');

        return in_array($sortField, $this->sortFields, true) ? $sort : '';
    }

    /**
     * Normalize cursor-based pagination parameters for sub-resources.
     *
     * @param array $query
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    private function normalizeCursorPaginationQuery(array $query): array
    {
        $normalized = [];

        if (array_key_exists('cursor', $query) && $query['cursor'] !== null) {
            $normalized['cursor'] = $query['cursor'];
        }

        if (array_key_exists('limit', $query) && $query['limit'] !== null) {
            $normalized['limit'] = (int) $query['limit'];
        }

        return $normalized;
    }
}
