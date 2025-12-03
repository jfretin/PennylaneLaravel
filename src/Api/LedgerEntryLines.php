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

    /**
     * Letter ledger entry lines together using the V2 API.
     *
     * @param array $ledger_entry_lines List of line ids (int) or arrays with an `id` key.
     * @param string $unbalanced_lettering_strategy Strategy to handle unbalanced letterings (none|partial).
     * @return array
     * @throws \RuntimeException When the API namespace is not V2.
     * @throws \InvalidArgumentException When parameters are invalid.
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function letter(array $ledger_entry_lines, string $unbalanced_lettering_strategy = 'none'): array
    {
        if (!$this->isV2()) {
            throw new \RuntimeException('Ledger entry line lettering is only available with the V2 API.');
        }

        $allowed_strategies = ['none', 'partial'];
        if (!in_array($unbalanced_lettering_strategy, $allowed_strategies, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Unsupported unbalanced lettering strategy "%s".',
                $unbalanced_lettering_strategy
            ));
        }

        if (count($ledger_entry_lines) < 2) {
            throw new \InvalidArgumentException('At least two ledger entry lines are required to letter.');
        }

        $normalized_lines = array_map(function ($line): array {
            if (is_array($line) && isset($line['id'])) {
                return ['id' => (int) $line['id']];
            }

            if (is_scalar($line)) {
                return ['id' => (int) $line];
            }

            throw new \InvalidArgumentException('Each ledger entry line must be an id or an array containing an id.');
        }, $ledger_entry_lines);

        $response = $this->client->request('post', $this->getNamespace() . 'ledger_entry_lines/lettering', [
            'json' => [
                'unbalanced_lettering_strategy' => $unbalanced_lettering_strategy,
                'ledger_entry_lines'           => $normalized_lines,
            ]
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
