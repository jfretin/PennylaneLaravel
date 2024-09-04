<?php

namespace Ashraam\PennylaneLaravel\Api;

class LedgerAccounts extends BaseApiV2
{
    use Filterable;

    private $filter_fields = [
        'number',
    ];

    /**
     * List all entries
     *
     * @return array
     */
    public function list($page = 1, $per_page = 20, $filters = [])
    {
        $query = [
            'page'     => $page,
            'per_page' => $per_page,
        ];
        $filter = $this->get_filters($filters);
        if ($filter != '') {
            $query['filter'] = $filter;
        }

        $query_string = http_build_query($query);
        $response = $this->client->request('get', self::API_NAMESPACE . "ledger_accounts?" . $query_string);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get an entry
     *
     * @return array
     */
    public function get($id)
    {
        $response = $this->client->request('get', self::API_NAMESPACE . "ledger_accounts/$id");

        return json_decode($response->getBody()->getContents(), true);
    }
}
