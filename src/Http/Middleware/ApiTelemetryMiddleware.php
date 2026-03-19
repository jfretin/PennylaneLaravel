<?php

namespace Ashraam\PennylaneLaravel\Http\Middleware;

use Throwable;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Ashraam\PennylaneLaravel\Telemetry\ApiCallTelemetryPayload;
use Ashraam\PennylaneLaravel\Events\ApiCallTelemetryCaptured;

class ApiTelemetryMiddleware
{
    private string $apiVersion;

    public function __construct(string $apiVersion = 'v2')
    {
        $this->apiVersion = $apiVersion;
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
            $startedAt = microtime(true);
            $requestId = $this->generateRequestId();

            return $handler($request, $options)->then(
                function (ResponseInterface $response) use ($request, $startedAt, $requestId) {
                    $this->dispatchTelemetry($request, $response, null, $startedAt, $requestId);
                    return $response;
                },
                function ($reason) use ($request, $startedAt, $requestId) {
                    $response = null;
                    $errorType = null;

                    if ($reason instanceof RequestException && $reason->hasResponse()) {
                        $response = $reason->getResponse();
                    }

                    if ($reason instanceof Throwable) {
                        $errorType = get_class($reason);
                    } elseif (is_object($reason)) {
                        $errorType = get_class($reason);
                    } else {
                        $errorType = gettype($reason);
                    }

                    $this->dispatchTelemetry($request, $response, $errorType, $startedAt, $requestId);

                    throw $reason;
                }
            );
        };
    }

    private function dispatchTelemetry(
        RequestInterface $request,
        ?ResponseInterface $response,
        ?string $errorType,
        float $startedAt,
        string $requestId
    ): void {
        if (!function_exists('event')) {
            return;
        }

        $finishedAt = microtime(true);
        $durationMs = (int) round(($finishedAt - $startedAt) * 1000);

        $payload = ApiCallTelemetryPayload::make([
            'request_id' => $requestId,
            'api_version' => $this->apiVersion,
            'method' => strtoupper($request->getMethod()),
            'endpoint' => $request->getUri()->getPath(),
            'use_2026_api_changes' => $this->resolve2026ApiChangesFlag($request),
            'status_code' => $response ? $response->getStatusCode() : null,
            'started_at' => gmdate('Y-m-d H:i:s', (int) $startedAt),
            'finished_at' => gmdate('Y-m-d H:i:s', (int) $finishedAt),
            'duration_ms' => $durationMs,
            'token_hash' => $this->resolveTokenHash($request),
            'ratelimit_limit' => $this->parseIntHeader($response, 'ratelimit-limit'),
            'ratelimit_remaining' => $this->parseIntHeader($response, 'ratelimit-remaining'),
            'ratelimit_reset' => $this->parseIntHeader($response, 'ratelimit-reset'),
            'retry_after' => $this->parseIntHeader($response, 'retry-after'),
            'error_type' => $errorType,
        ]);

        event(new ApiCallTelemetryCaptured($payload));
    }

    private function resolveTokenHash(RequestInterface $request): ?string
    {
        $authorization = $request->getHeaderLine('Authorization');
        if ($authorization === '') {
            return null;
        }

        if (stripos($authorization, 'Bearer ') !== 0) {
            return null;
        }

        $token = trim(substr($authorization, 7));
        if ($token === '') {
            return null;
        }

        return hash('sha256', $token);
    }

    private function resolve2026ApiChangesFlag(RequestInterface $request): ?bool
    {
        $header = trim($request->getHeaderLine('X-Use-2026-API-Changes'));
        if ($header === '') {
            return null;
        }

        return filter_var($header, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    private function parseIntHeader(?ResponseInterface $response, string $header): ?int
    {
        if (!$response) {
            return null;
        }

        $value = trim($response->getHeaderLine($header));
        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function generateRequestId(): string
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (Throwable) {
            return uniqid('pl_', true);
        }
    }
}
