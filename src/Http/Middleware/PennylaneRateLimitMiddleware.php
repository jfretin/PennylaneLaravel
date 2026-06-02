<?php

namespace Ashraam\PennylaneLaravel\Http\Middleware;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PennylaneRateLimitMiddleware
{
    private int $maxRetries;
    private int $jitterMs;
    private int $maxDelayMs;
    private int $fallbackDelayMs;

    /**
     * @param array{max_retries?: mixed, jitter_ms?: mixed, max_delay_ms?: mixed, fallback_delay_ms?: mixed} $config
     */
    public function __construct(array $config = [])
    {
        $this->maxRetries = max(0, (int) ($config['max_retries'] ?? 2));
        $this->jitterMs = max(0, (int) ($config['jitter_ms'] ?? 250));
        $this->maxDelayMs = max(0, (int) ($config['max_delay_ms'] ?? 5000));
        $this->fallbackDelayMs = max(0, (int) ($config['fallback_delay_ms'] ?? 1000));
    }

    public function __invoke(callable $handler): callable
    {
        $middleware = null;
        $middleware = function (RequestInterface $request, array $options) use ($handler, &$middleware) {
            $retries = (int) ($options['pennylane_rate_limit_retries'] ?? 0);

            return $handler($request, $options)->then(
                function (ResponseInterface $response) use ($request, $options, &$middleware, $retries) {
                    if (!$this->shouldRetry($request, $retries, $response)) {
                        return $response;
                    }

                    return $this->retry($request, $options, $middleware, $retries, $response);
                },
                function ($reason) use ($request, $options, &$middleware, $retries) {
                    if (
                        !$reason instanceof RequestException
                        || !$reason->hasResponse()
                        || !$this->shouldRetry($request, $retries, $reason->getResponse())
                    ) {
                        throw $reason;
                    }

                    return $this->retry($request, $options, $middleware, $retries, $reason->getResponse());
                }
            );
        };

        return $middleware;
    }

    private function shouldRetry(RequestInterface $request, int $retries, ?ResponseInterface $response): bool
    {
        if (!$this->isRetryableMethod($request) || $retries >= $this->maxRetries || !$response) {
            return false;
        }

        return $response->getStatusCode() === 429;
    }

    private function isRetryableMethod(RequestInterface $request): bool
    {
        return in_array(strtoupper($request->getMethod()), ['GET', 'HEAD', 'PUT'], true);
    }

    private function retry(
        RequestInterface $request,
        array $options,
        callable $middleware,
        int $retries,
        ResponseInterface $response
    ) {
        $delayMs = $this->resolveDelayMs($response);
        usleep($delayMs * 1000);

        $options['pennylane_rate_limit_retries'] = $retries + 1;

        return $middleware($request, $options);
    }

    private function resolveDelayMs(ResponseInterface $response): int
    {
        $baseDelayMs = $this->resolveRetryAfterMs($response)
            ?? $this->resolveResetDelayMs($response)
            ?? $this->fallbackDelayMs;

        $baseDelayMs = max(0, min($baseDelayMs, $this->maxDelayMs));
        $jitterMs = $this->resolveJitterMs($baseDelayMs);

        return max(0, min($baseDelayMs + $jitterMs, $this->maxDelayMs));
    }

    private function resolveRetryAfterMs(ResponseInterface $response): ?int
    {
        $value = trim($response->getHeaderLine('retry-after'));
        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        return (int) max(0, ceil((float) $value * 1000));
    }

    private function resolveResetDelayMs(ResponseInterface $response): ?int
    {
        $value = trim($response->getHeaderLine('ratelimit-reset'));
        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        $resetAt = (int) $value;
        $delayMs = max(0, ($resetAt - time()) * 1000);

        return $delayMs > 0 ? $delayMs : null;
    }

    private function resolveJitterMs(int $baseDelayMs): int
    {
        if ($baseDelayMs <= 0 || $this->jitterMs <= 0) {
            return 0;
        }

        $maxJitter = min($this->jitterMs, max(0, $this->maxDelayMs - $baseDelayMs));
        if ($maxJitter <= 0) {
            return 0;
        }

        try {
            return random_int(0, $maxJitter);
        } catch (\Throwable) {
            return $maxJitter;
        }
    }
}
