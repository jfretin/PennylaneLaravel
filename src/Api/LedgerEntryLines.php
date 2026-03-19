<?php

namespace Ashraam\PennylaneLaravel\Api;

class LedgerEntryLines extends BaseApi
{
    protected $defaultNamespace = self::API_NAMESPACE_V2;

    use Filterable;

    private $filter_fields = [
        'ledger_account_id',
    ];

    private $sort_fields = [
        'id',
    ];

    /**
     * List ledger entry lines of a ledger entry.
     *
     * @param int|string $ledger_entry_id
     * @param int $page Ignored when a cursor is provided.
     * @param int $per_page Items per page / cursor page size.
     * @param array $filters Optional filters.
     * @param string|null $sort Optional sort field.
     * @param string|null $cursor Optional cursor for V2 pagination.
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function list($ledger_entry_id, $page = 1, $per_page = 20, array $filters = [], ?string $sort = null, ?string $cursor = null)
    {
        if ($ledger_entry_id == '') {
            return null;
        }

        $query = [];
        $filter = $this->get_filters($filters);
        $sort = $this->get_sort($sort);
        $useCursorPagination = $this->isV2() && $this->uses2026ApiChanges() && func_num_args() >= 6;

        if ($useCursorPagination) {
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

        $queryString = http_build_query($query);
        return $this->requestJson('get', $this->getNamespace() . "ledger_entries/$ledger_entry_id/ledger_entry_lines" . ($queryString ? ('?' . $queryString) : ''));
    }

    /**
     * List ledger entry lines lettered to a given ledger entry line.
     *
     * @param int|string $ledger_entry_line_id
     * @param int $page Ignored when a cursor is provided.
     * @param int $per_page Items per page / cursor page size.
     * @param string|null $sort Optional sort field.
     * @param string|null $cursor Optional cursor for V2 pagination.
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function listLinked($ledger_entry_line_id, $page = 1, $per_page = 20, ?string $sort = null, ?string $cursor = null)
    {
        if ($ledger_entry_line_id == '') {
            return null;
        }

        $query = [];
        $sort = $this->get_sort($sort);
        $useCursorPagination = $this->isV2() && $this->uses2026ApiChanges() && func_num_args() >= 5;

        if ($useCursorPagination) {
            $query['limit'] = $per_page;
            if ($cursor !== null) {
                $query['cursor'] = $cursor;
            }
        } else {
            $query['page'] = $page;
            $query['per_page'] = $per_page;
        }

        if ($sort != '') {
            $query['sort'] = $sort;
        }

        $queryString = http_build_query($query);
        return $this->requestJson('get', $this->getNamespace() . "ledger_entry_lines/$ledger_entry_line_id/lettered_ledger_entry_lines" . ($queryString ? ('?' . $queryString) : ''));
    }

    /**
     * Letter ledger entry lines together using the V2 API.
     *
     * @param array $ledger_entry_lines List of line ids (int) or arrays with an `id` key.
     * @param string $unbalanced_lettering_strategy Strategy to handle unbalanced letterings (none|partial).
     * @return array
     * @throws \RuntimeException When the API namespace is not V2.
     * @throws \InvalidArgumentException When parameters are invalid.
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function letter(array $ledger_entry_lines, string $unbalanced_lettering_strategy = 'none'): array
    {
        if (!$this->isV2()) {
            throw new \RuntimeException('Ledger entry line lettering is only available with the V2 API.');
        }

        $allowed_strategies = ['none', 'partial'];
        if (!in_array($unbalanced_lettering_strategy, $allowed_strategies, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported unbalanced lettering strategy "%s".',
                $unbalanced_lettering_strategy
            ));
        }

        if (count($ledger_entry_lines) < 2) {
            throw new \InvalidArgumentException('At least two ledger entry lines are required to letter.');
        }

        $normalized_lines = array_map(function ($line): array {
            if (is_array($line) && isset($line['id'])) {
                return ['id' => (int) $line['id']];
            }

            if (is_scalar($line)) {
                return ['id' => (int) $line];
            }

            throw new \InvalidArgumentException('Each ledger entry line must be an id or an array containing an id.');
        }, $ledger_entry_lines);

        return $this->requestJson('post', $this->getNamespace() . 'ledger_entry_lines/lettering', [
            'json' => [
                'unbalanced_lettering_strategy' => $unbalanced_lettering_strategy,
                'ledger_entry_lines'           => $normalized_lines,
            ]
        ]);
    }

    /**
     * Retrieve a ledger entry line by its identifier (V2 only).
     *
     * @param int|string $ledger_entry_line_id
     * @return array
     * @throws \RuntimeException When the API namespace is not V2.
     * @throws \InvalidArgumentException When $ledger_entry_line_id is empty.
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function get(int|string $ledger_entry_line_id): array
    {
        if (!$this->isV2()) {
            throw new \RuntimeException('Ledger entry line retrieval is only available with the V2 API.');
        }

        if ($ledger_entry_line_id === '' || $ledger_entry_line_id === null) {
            throw new \InvalidArgumentException('A ledger entry line id must be provided.');
        }

        $endpoint = sprintf('%sledger_entry_lines/%s', $this->getNamespace(), $ledger_entry_line_id);
        return $this->requestJson('get', $endpoint);
    }
}
