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
        $response = $this->client->request('get', $this->getNamespace() . "ledger_entries?" . $query_string);

        return json_decode($response->getBody()->getContents(), true);
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
        $response = $this->client->request('get', $endpoint);

        return json_decode($response->getBody()->getContents(), true);
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

        $response = $this->client->request('post', $this->getNamespace() . 'ledger_entries', [
            'json' => $payload,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
