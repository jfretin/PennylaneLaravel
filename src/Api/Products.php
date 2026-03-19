<?php

namespace Ashraam\PennylaneLaravel\Api;

class Products extends BaseApi
{
    use Filterable;

    private $filter_fields = [
        'id',
        'label',
        'reference',
        'external_reference',
    ];

    private $sort_fields = [
        'id',
    ];

    /**
     * List products.
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

        return $this->requestJson('get', $this->getNamespace() . "products" . ($query_string ? ('?' . $query_string) : ''));
    }


    /**
     * Create a new product
     *
     * @param array $data
     * @return array
     */
    public function create(array $data)
    {
        $payload = $this->buildPayload($data, 'product');
        return $this->requestJson('post', $this->getNamespace() . "products", [
            'json' => $payload,
        ]);
    }


    /**
     * Retrieve a product by it's ID
     *
     * @param string $id
     * @return array
     */
    public function get(string $id)
    {
        return $this->requestJson('get', $this->getNamespace() . "products/{$id}");
    }


    /**
     * Update a product by it's ID
     *
     * @param string $id
     * @param array $data
     * @return array
     */
    public function update(string $id, array $data)
    {
        $payload = $this->buildPayload($data, 'product');
        return $this->requestJson('put', $this->getNamespace() . "products/{$id}", [
            'json' => $payload,
        ]);
    }
}
