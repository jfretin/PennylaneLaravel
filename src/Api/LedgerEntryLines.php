<?php

namespace Ashraam\PennylaneLaravel\Api;

class LedgerEntryLines extends BaseApi
{
    protected $defaultNamespace = self::API_NAMESPACE_V2;
    /**
     * List all entries
     * 
     * @return array
     */
    public function list($ledger_entry_id, $page = 1, $per_page = 20)
    {
        if ($ledger_entry_id == '') {
            return null;
        }
        $response = $this->client->request('get', $this->getNamespace() . "ledger_entries/$ledger_entry_id/ledger_entry_lines?page=$page&per_page=$per_page");

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * List all entries
     *
     * @return array
     */
    public function listLinked($ledger_entry_line_id, $page = 1, $per_page = 20)
    {
        if ($ledger_entry_line_id == '') {
            return null;
        }
        $response = $this->client->request('get', $this->getNamespace() . "ledger_entry_lines/$ledger_entry_line_id/lettered_ledger_entry_lines?page=$page&per_page=$per_page");

        return json_decode($response->getBody()->getContents(), true);
    }
}
