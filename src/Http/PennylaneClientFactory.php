<?php

namespace Ashraam\PennylaneLaravel\Http;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Ashraam\PennylaneLaravel\Http\Middleware\ApiTelemetryMiddleware;
use Ashraam\PennylaneLaravel\Http\Middleware\PennylaneRateLimitMiddleware;

class PennylaneClientFactory
{
    public static function createClient(array $config, bool $enableTelemetry = false, string $apiVersion = 'v1'): Client
    {
        $enableRateLimit = static::shouldEnableRateLimitMiddleware($apiVersion);

        if (!$enableTelemetry && !$enableRateLimit) {
            return new Client($config);
        }

        $handler = $config['handler'] ?? null;
        $stack = $handler instanceof HandlerStack ? $handler : HandlerStack::create($handler);
        if ($enableRateLimit) {
            try {
                $stack->before(
                    'http_errors',
                    new PennylaneRateLimitMiddleware(static::rateLimitConfig()),
                    'pennylane_api_rate_limit'
                );
            } catch (\InvalidArgumentException) {
                $stack->push(new PennylaneRateLimitMiddleware(static::rateLimitConfig()), 'pennylane_api_rate_limit');
            }
        }

        if ($enableTelemetry) {
            $stack->push(new ApiTelemetryMiddleware($apiVersion), 'pennylane_api_telemetry');
        }

        $config['handler'] = $stack;

        return new Client($config);
    }

    /**
     * Exposed for tests to verify stack composition decisions.
     *
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public static function shouldEnableRateLimitMiddleware(string $apiVersion): bool
    {
        return $apiVersion === 'v2' && (bool) config('pennylane-laravel.rate_limit.enabled', true);
    }

    /**
     * Exposed for tests and middleware construction.
     *
     * @return array<string, mixed>
     * @author Jonathan F. <jonathan.f@mistersmoke.com>
     */
    public static function rateLimitConfig(): array
    {
        $config = config('pennylane-laravel.rate_limit', []);

        return is_array($config) ? $config : [];
    }
}
