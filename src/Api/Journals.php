<?php

namespace Ashraam\PennylaneLaravel\Api;

class Journals extends BaseApi
{
    protected $defaultNamespace = self::API_NAMESPACE_V2;

    use Filterable;

    private $filter_fields = [
        'type',
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
        return $this->requestJson('get', $this->getNamespace() . "journals?" . $query_string);
    }
}
