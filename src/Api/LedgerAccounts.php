<?php

namespace Ashraam\PennylaneLaravel\Api;

class LedgerAccounts extends BaseApi
{
    protected $defaultNamespace = self::API_NAMESPACE_V2;
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
        return $this->requestJson('get', $this->getNamespace() . "ledger_accounts?" . $query_string);
    }

    /**
     * Get an entry
     *
     * @return array
     */
    public function get($id)
    {
        return $this->requestJson('get', $this->getNamespace() . "ledger_accounts/$id");
    }

    /**
     * Create a ledger account.
     */
    public function create(array $payload)
    {
        return $this->requestJson('post', $this->getNamespace() . 'ledger_accounts', [
            'json' => $payload,
        ]);
    }
}
