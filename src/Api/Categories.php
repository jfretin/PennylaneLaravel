<?php

namespace Ashraam\PennylaneLaravel\Api;

class Categories extends BaseApi
{
    private array $sort_fields_v1 = [
        'id',
        'group_id',
        'label',
        'direction',
    ];

    private array $filter_fields_v1 = [
        'id',
        'group_id',
        'label',
        'direction',
    ];

    private array $sort_fields_v2 = [
        'id',
    ];

    private array $filter_fields_v2 = [
        'id',
        'label',
        'category_group_id',
        'analytical_code',
    ];

    /**
     * Retrieve a category by its ID.
     *
     * @param string $id
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function get(string $id)
    {
        return $this->requestJson('get', $this->getNamespace() . "categories/{$id}");
    }

    /**
     * List categories.
     * - V1: page-based pagination (params: $page, $per_page, $filters, $sort)
     * - V2: cursor-based pagination (params: $cursor, $limit, $filters, $sort)
     *
     * @param int $page Ignored on V2, kept for backward compatibility.
     * @param int $per_page V2 limit value, V1 per-page value.
     * @param array $filters Array of filters to forward.
     * @param string $sort Sort field, optionally prefixed with `-`.
     * @param string|null $cursor Cursor for V2 pagination.
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function list($page = 1, $per_page = 20, $filters = [], $sort = '', ?string $cursor = null)
    {
        $filter = $this->filterQuery($filters, $this->isV2() ? $this->filter_fields_v2 : $this->filter_fields_v1);
        $sort = $this->sortQuery($sort, $this->isV2() ? $this->sort_fields_v2 : $this->sort_fields_v1);
        $query = [
        ];
        $useCursorPagination = $this->isV2() && func_num_args() >= 5;

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

        $query_string = http_build_query($query);
        return $this->requestJson('get', $this->getNamespace() . "categories?" . $query_string);
    }

    /**
     * Normalize category filters against the allowed field list for the active API version.
     *
     * @param array $filters
     * @param array $allowedFields
     * @return string
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    private function filterQuery(array $filters, array $allowedFields): string
    {
        $query = [];

        foreach ($filters as $filter) {
            if (!is_array($filter)) {
                continue;
            }

            $field = $filter['field'] ?? null;
            if (!is_string($field) || !in_array($field, $allowedFields, true)) {
                continue;
            }

            $query[] = [
                'field' => $field,
                'operator' => $filter['operator'] ?? null,
                'value' => $filter['value'] ?? null,
            ];
        }

        return !empty($query) ? json_encode($query) : '';
    }

    /**
     * Normalize category sort values against the allowed field list for the active API version.
     *
     * @param string|null $sort
     * @param array $allowedFields
     * @return string
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    private function sortQuery($sort, array $allowedFields): string
    {
        if ($sort === null || $sort === '') {
            return '';
        }

        $sortField = ltrim((string) $sort, '-');
        return in_array($sortField, $allowedFields, true) ? (string) $sort : '';
    }
}
