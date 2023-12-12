<?php

namespace Ashraam\PennylaneLaravel\Api;

class LedgerEntryLines extends BaseApiV2
{
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
        $response = $this->client->request('get', self::API_NAMESPACE . "ledger_entries/$ledger_entry_id/ledger_entry_lines?page=$page&per_page=$per_page");

        return json_decode($response->getBody()->getContents(), true);
    }
}
