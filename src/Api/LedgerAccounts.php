<?php

namespace Ashraam\PennylaneLaravel\Api;

class LedgerAccounts extends BaseApi
{
    protected $defaultNamespace = self::API_NAMESPACE_V2;

    use Filterable;

    private $filter_fields = [
        'number',
    ];

    private $sort_fields = [
        'id',
    ];

    /**
     * List ledger accounts.
     * - V2 old behavior: page-based pagination
     * - V2 new behavior: cursor-based pagination
     *
     * @param int $page Ignored when a cursor is provided.
     * @param int $per_page Items per page / cursor page size.
     * @param array $filters Array of filters.
     * @param string|null $sort Sort field, optionally prefixed with `-`.
     * @param string|null $cursor Cursor for V2 pagination.
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function list($page = 1, $per_page = 20, $filters = [], ?string $sort = null, ?string $cursor = null)
    {
        $filter = $this->get_filters($filters);
        $sort = $this->get_sort($sort);
        $query = [];
        $useCursorPagination = $this->isV2() && $this->uses2026ApiChanges() && func_num_args() >= 5;

        if ($useCursorPagination) {
            // The public method keeps the legacy $per_page argument, but V2 expects it as `limit`.
            $query['limit'] = $per_page;
            if ($cursor !== null) {
                $query['cursor'] = $cursor;
            }
        } else {
            $query['page'] = $page;
            $query['per_page'] = $per_page;
        }

        if ($filter != '') {
            $query['filter'] = $filter;
        }
        if ($sort != '') {
            $query['sort'] = $sort;
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
