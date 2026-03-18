<?php

namespace Ashraam\PennylaneLaravel\Api;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

abstract class BaseApi
{
    const API_NAMESPACE_V1 = 'v1/';
    const API_NAMESPACE_V2 = 'v2/';
    protected $client;
    protected $namespace;
    protected $defaultNamespace = self::API_NAMESPACE_V1;
    protected ?bool $use2026ApiChangesOverride = null;

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
     * Clone the current API resource with a request-scoped 2026 behavior override.
     *
     * @param bool|null $enabled
     * @return static
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public function with2026ApiChanges(?bool $enabled)
    {
        $clone = clone $this;
        $clone->use2026ApiChangesOverride = $enabled;

        return $clone;
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

    /**
     * Execute an HTTP request, applying the 2026 API header override when needed.
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    protected function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        if ($this->isV2() && $this->use2026ApiChangesOverride !== null) {
            $options['headers'] = array_merge(
                $options['headers'] ?? [],
                ['X-Use-2026-API-Changes' => $this->use2026ApiChangesOverride ? 'true' : 'false']
            );
        }

        return $this->client->request($method, $uri, $options);
    }

    /**
     * Execute an HTTP request and decode the JSON response body.
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return array
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    protected function requestJson(string $method, string $uri, array $options = []): array
    {
        $response = $this->request($method, $uri, $options);

        return json_decode($response->getBody()->getContents(), true);
    }
}
