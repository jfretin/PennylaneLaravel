<?php

namespace Ashraam\PennylaneLaravel\Api;

class LedgerAccounts extends BaseApiV2
{
    /**
     * List all entries
     *
     * @return array
     */
    public function list($page = 1, $per_page = 20)
    {
        $response = $this->client->request('get', self::API_NAMESPACE . "ledger_accounts?page=$page&per_page=$per_page");

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
