<?php

namespace Ashraam\PennylaneLaravel\Api;

class BankAccounts extends BaseApi
{
    private array $sortFields = [
        'id',
    ];

    /**
     * List bank accounts.
     *
     * @param int $page Ignored for V2-only resource, kept for signature consistency.
     * @param int $per_page Number of items requested through the `limit` query parameter.
     * @param array $filters Ignored because the API does not document bank account filters.
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

        $normalizedSort = $this->normalizeSort($sort);
        if ($normalizedSort !== '') {
            $query['sort'] = $normalizedSort;
        }

        $queryString = http_build_query($query);

        return $this->requestJson('get', $this->getNamespace() . 'bank_accounts' . ($queryString ? ('?' . $queryString) : ''));
    }

    /**
     * Retrieve a bank account by ID.
     *
     * @param int|string $id
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function get(int|string $id): array
    {
        $this->assertV2Only();

        return $this->requestJson('get', $this->getNamespace() . "bank_accounts/{$id}");
    }

    /**
     * Create a bank account.
     *
     * @param array $data
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function create(array $data): array
    {
        $this->assertV2Only();

        return $this->requestJson('post', $this->getNamespace() . 'bank_accounts', [
            'json' => $data,
        ]);
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
            throw new \RuntimeException('Bank account endpoints are only available with the V2 API.');
        }
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
}
