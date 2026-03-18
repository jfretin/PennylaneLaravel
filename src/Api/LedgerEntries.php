<?php

namespace Ashraam\PennylaneLaravel\Api;

class LedgerEntries extends BaseApi
{
    protected $defaultNamespace = self::API_NAMESPACE_V2;

    use Filterable;

    private $sort_fields = [
        'updated_at',
        'created_at',
        'date',
    ];

    private $filter_fields = [
        'updated_at',
        'created_at',
        'journal_id',
        'date',
    ];

    /**
     * List all entries
     *
     * @return array
     */
    public function list($page = 1, $per_page = 20, $filters = [], $sort = '')
    {
        $filter = $this->get_filters($filters);
        $sort = $this->get_sort($sort);
        $query = [
            'page'     => $page,
            'per_page' => $per_page,
        ];
        if ($filter != '') {
            $query['filter'] = $filter;
        }
        if ($sort != '') {
            $query['sort'] = $sort;
        }
        $query_string = http_build_query($query);
        return $this->requestJson('get', $this->getNamespace() . "ledger_entries?" . $query_string);
    }

    /**
     * Retrieve a single ledger entry via the V2 API.
     *
     * @param int|string $ledgerEntryId
     * @return array
     * @throws \RuntimeException When the API namespace is not V2
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function get(int|string $ledgerEntryId): array
    {
        if ($ledgerEntryId === '' || $ledgerEntryId === null) {
            throw new \InvalidArgumentException('Ledger entry id is required.');
        }

        if (!$this->isV2()) {
            throw new \RuntimeException('Ledger entry retrieval is only available with the V2 API.');
        }

        $endpoint = sprintf('%sledger_entries/%s', $this->getNamespace(), $ledgerEntryId);
        return $this->requestJson('get', $endpoint);
    }

    /**
     * Create a ledger entry using the V2 API.
     *
     * @param array $ledger_entry
     * @return array
     * @throws \RuntimeException When the API namespace is not V2
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function create(array $ledger_entry): array
    {
        if (!$this->isV2()) {
            throw new \RuntimeException('Ledger entry creation is only available with the V2 API.');
        }

        $payload = $this->buildPayload(['ledger_entry' => $ledger_entry], 'ledger_entry');

        return $this->requestJson('post', $this->getNamespace() . 'ledger_entries', [
            'json' => $payload,
        ]);
    }

    /**
     * Update a ledger entry using the V2 API.
     *
     * @param int|string $ledgerEntryId
     * @param array $ledger_entry
     * @return array
     * @throws \RuntimeException When the API namespace is not V2
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function update(int|string $ledgerEntryId, array $ledger_entry): array
    {
        if ($ledgerEntryId === '' || $ledgerEntryId === null) {
            throw new \InvalidArgumentException('Ledger entry id is required.');
        }

        if (!$this->isV2()) {
            throw new \RuntimeException('Ledger entry update is only available with the V2 API.');
        }

        $payload = $this->buildPayload(['ledger_entry' => $ledger_entry], 'ledger_entry');
        $endpoint = sprintf('%sledger_entries/%s', $this->getNamespace(), $ledgerEntryId);

        return $this->requestJson('put', $endpoint, [
            'json' => $payload,
        ]);
    }

    /**
     * Delete a ledger entry using the V2 API.
     *
     * @param int|string $ledgerEntryId
     * @return array
     * @throws \RuntimeException When the API namespace is not V2
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function delete(int|string $ledgerEntryId): array
    {
        if ($ledgerEntryId === '' || $ledgerEntryId === null) {
            throw new \InvalidArgumentException('Ledger entry id is required.');
        }

        if (!$this->isV2()) {
            throw new \RuntimeException('Ledger entry deletion is only available with the V2 API.');
        }

        $endpoint = sprintf('%sledger_entries/%s', $this->getNamespace(), $ledgerEntryId);
        return $this->requestJson('delete', $endpoint);
    }
}
