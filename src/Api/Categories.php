<?php

namespace Ashraam\PennylaneLaravel\Api;

class Categories extends BaseApiV1
{
    use Filterable;

    private $sort_fields = [
        'id',
        'group_id',
        'label',
        'direction',
    ];

    private $filter_fields = [
        'id',
        'group_id',
        'label',
        'direction',
    ];

    /**
     * Retrieve a category by its ID
     *
     * @param string $id
     * @param string $locale
     * @return array
     */
    public function get(string $id)
    {
        $response = $this->client->request('get', self::API_NAMESPACE . "categories/{$id}");

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Retrieve a category by its ID
     *
     * @param string $id
     * @param string $locale
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
        $response = $this->client->request('get', self::API_NAMESPACE . "categories?" . $query_string);

        return json_decode($response->getBody()->getContents(), true);
    }
}
