<?php

namespace Ashraam\PennylaneLaravel\Api;

class LedgerEntries extends BaseApiV2
{

    use Filterable;

    private $sort_fields = [
        'updated_at',
        'created_at',
    ];

    private $filter_fields = [
        'updated_at',
        'created_at',
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
        $response = $this->client->request('get', self::API_NAMESPACE . "ledger_entries?" . $query_string);

        return json_decode($response->getBody()->getContents(), true);
    }
}
