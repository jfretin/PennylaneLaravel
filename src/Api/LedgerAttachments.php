<?php

namespace Ashraam\PennylaneLaravel\Api;

class LedgerAttachments extends BaseApiV2
{
    /**
     * List all entries
     *
     * @return array
     */
    public function list($page = 1, $per_page = 20)
    {
        $response = $this->client->request('get', self::API_NAMESPACE . "ledger_attachments?page=$page&per_page=$per_page");

        return json_decode($response->getBody()->getContents(), true);
    }
}
