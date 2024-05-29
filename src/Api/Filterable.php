<?php
namespace Ashraam\PennylaneLaravel\Api;

trait Filterable {
    //?page=11&per_page=25&filter=%5B%7B%22field%22%3A%22updated_at%22%2C%22operator%22%3A%20%22gt%22%2C%22value%22%3A%222024-05-27T00%3A00%3A00Z%22%7D%5D&sort=updated_at', [
        // [{"field":"updated_at","operator": "gt","value":"2024-05-27T00:00:00Z"}]
    public function get_filters($filters) {
        $query = [];
        if (count($filters) > 0 ) {
            foreach ($filters as $filter) {
                if (in_array($filter['field'], $this->filter_fields)) {
                    $query[] = [
                        'field'    => $filter['field'],
                        'operator' => $filter['operator'],
                        'value'    => $filter['value'],
                    ];
                }
            }
            return (!empty($query) ? json_encode($query) : '');
        }
        return '';
    }

    public function get_sort($sort) {
        $query = '';
        if ($sort != '') {
            $sort_field = ltrim($sort, '-');
            if (in_array($sort_field, $this->sort_fields)) {
                $query = $sort;
            }
        }
        return $query;
    }
}
