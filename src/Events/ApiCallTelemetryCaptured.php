<?php

namespace Ashraam\PennylaneLaravel\Events;

use Ashraam\PennylaneLaravel\Telemetry\ApiCallTelemetryPayload;

class ApiCallTelemetryCaptured
{
    private ApiCallTelemetryPayload $payload;

    public function __construct(ApiCallTelemetryPayload $payload)
    {
        $this->payload = $payload;
    }

    public function payload(): ApiCallTelemetryPayload
    {
        return $this->payload;
    }

    public function toArray(): array
    {
        return $this->payload->toArray();
    }
}
