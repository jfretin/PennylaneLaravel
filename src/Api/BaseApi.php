<?php

namespace Ashraam\PennylaneLaravel\Api;

use GuzzleHttp\ClientInterface;

abstract class BaseApi
{
    const API_NAMESPACE_V1 = 'v1/';
    const API_NAMESPACE_V2 = 'v2/';
    protected $client;
    protected $namespace;
    protected $defaultNamespace = self::API_NAMESPACE_V1;

    public function __construct(ClientInterface $client, $override_namespace = null)
    {
        $this->client = $client;
        if ($override_namespace) {
            $this->namespace = $override_namespace;
        }
    }

    public function getNamespace()
    {
        return $this->namespace ?: $this->defaultNamespace;
    }

    public function isV2(): bool
    {
        return strpos($this->getNamespace(), self::API_NAMESPACE_V2) === 0;
    }

    /**
     * Build payload by applying a version-specific root envelope.
     * - V1: ensures payload is wrapped under $envelope if not already
     * - V2: unwraps $envelope to top-level while preserving other keys
     */
    protected function buildPayload(array $data, string $envelope): array
    {
        if ($this->isV2()) {
            if (array_key_exists($envelope, $data) && is_array($data[$envelope])) {
                $root = $data;
                $wrapped = $root[$envelope];
                unset($root[$envelope]);
                return array_merge($root, $wrapped);
            }
            return $data;
        }

        if (array_key_exists($envelope, $data)) {
            return $data;
        }
        return [$envelope => $data];
    }
}
