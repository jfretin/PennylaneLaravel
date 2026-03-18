<?php

namespace Ashraam\PennylaneLaravel\Api;

use DateTimeInterface;

class Changelogs extends BaseApi
{
    protected $defaultNamespace = self::API_NAMESPACE_V2;

    /**
     * Retrieve supplier invoice changelog events.
     *
     * @param array $parameters Optional query parameters (cursor, limit, start_date)
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function supplierInvoices(array $parameters = []): array
    {
        return $this->fetch('supplier_invoices', $parameters);
    }

    /**
     * Retrieve customer invoice changelog events.
     *
     * @param array $parameters Optional query parameters (cursor, limit, start_date)
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function customerInvoices(array $parameters = []): array
    {
        return $this->fetch('customer_invoices', $parameters);
    }

    /**
     * Retrieve customer changelog events.
     *
     * @param array $parameters Optional query parameters (cursor, limit, start_date)
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function customers(array $parameters = []): array
    {
        return $this->fetch('customers', $parameters);
    }

    /**
     * Retrieve supplier changelog events.
     *
     * @param array $parameters Optional query parameters (cursor, limit, start_date)
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function suppliers(array $parameters = []): array
    {
        return $this->fetch('suppliers', $parameters);
    }

    /**
     * Retrieve product changelog events.
     *
     * @param array $parameters Optional query parameters (cursor, limit, start_date)
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function products(array $parameters = []): array
    {
        return $this->fetch('products', $parameters);
    }

    /**
     * Retrieve ledger entry line changelog events.
     *
     * @param array $parameters Optional query parameters (cursor, limit, start_date)
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function ledgerEntryLines(array $parameters = []): array
    {
        return $this->fetch('ledger_entry_lines', $parameters);
    }

    /**
     * Retrieve transaction changelog events.
     *
     * @param array $parameters Optional query parameters (cursor, limit, start_date)
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function transactions(array $parameters = []): array
    {
        return $this->fetch('transactions', $parameters);
    }

    /**
     * Base changelog request builder.
     *
     * @param string $resource Resource name expected by the API.
     * @param array $parameters User-provided query parameters.
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    protected function fetch(string $resource, array $parameters): array
    {
        if (!$this->isV2()) {
            throw new \RuntimeException('Changelog endpoints are only available with the V2 API.');
        }

        $query = $this->normalizeParameters($parameters);
        $queryString = $query ? ('?' . http_build_query($query)) : '';

        return $this->requestJson('get', sprintf(
            '%schangelogs/%s%s',
            $this->getNamespace(),
            $resource,
            $queryString
        ));
    }

    /**
     * Normalize supported parameters for changelog endpoints.
     *
     * @param array $parameters
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    private function normalizeParameters(array $parameters): array
    {
        $allowedKeys = ['cursor', 'limit', 'start_date'];
        $normalized = [];

        foreach ($allowedKeys as $key) {
            if (!array_key_exists($key, $parameters) || $parameters[$key] === null) {
                continue;
            }

            $value = $parameters[$key];

            if ($key === 'start_date' && $value instanceof DateTimeInterface) {
                $value = $value->format(DATE_ATOM);
            }

            if ($key === 'limit') {
                $value = (int)$value;
            }

            $normalized[$key] = $value;
        }

        return $normalized;
    }
}
