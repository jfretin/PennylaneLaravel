<?php

namespace Ashraam\PennylaneLaravel\Api;

class CustomerInvoiceTemplates extends BaseApi
{
    protected $defaultNamespace = self::API_NAMESPACE_V2;

    /**
     * List customer invoice templates (V2 only).
     *
     * @param int|null $limit
     * @param string|null $cursor
     * @param string|null $sort
     * @return array
     * @throws \RuntimeException When the API namespace is not V2.
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function list(?int $limit = 20, ?string $cursor = null, ?string $sort = null): array
    {
        if (!$this->isV2()) {
            throw new \RuntimeException('Customer invoice templates are only available with the V2 API.');
        }

        $query = [];
        if ($limit !== null) {
            $query['limit'] = $limit;
        }
        if ($cursor !== null) {
            $query['cursor'] = $cursor;
        }
        if ($sort !== null) {
            $query['sort'] = $sort;
        }

        $query_string = http_build_query($query);
        $endpoint = $this->getNamespace() . 'customer_invoice_templates' . ($query_string ? ('?' . $query_string) : '');
        return $this->requestJson('get', $endpoint);
    }
}
