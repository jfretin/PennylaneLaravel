<?php

namespace Ashraam\PennylaneLaravel\Api;

class Enums extends BaseApi
{
    /**
     * Retrieve enums by its ID
     *
     * @param string $id
     * @param string $locale
     * @return array
     */
    public function get(string $id, $locale = 'en')
    {
        return $this->requestJson('get', $this->getNamespace() . "enums/{$id}", [
            'query' => [
                'locale' => $locale
            ]
        ]);
    }
}
