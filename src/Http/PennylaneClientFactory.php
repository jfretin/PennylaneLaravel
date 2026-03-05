<?php

namespace Ashraam\PennylaneLaravel\Http;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Ashraam\PennylaneLaravel\Http\Middleware\ApiTelemetryMiddleware;

class PennylaneClientFactory
{
    public static function createClient(array $config, bool $enableTelemetry = false, string $apiVersion = 'v1'): Client
    {
        if (!$enableTelemetry) {
            return new Client($config);
        }

        $handler = $config['handler'] ?? null;
        $stack = $handler instanceof HandlerStack ? $handler : HandlerStack::create($handler);
        $stack->push(new ApiTelemetryMiddleware($apiVersion), 'pennylane_api_telemetry');

        $config['handler'] = $stack;

        return new Client($config);
    }
}
