<?php

namespace Ashraam\PennylaneLaravel\Telemetry;

class ApiCallTelemetryPayload
{
    private array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public static function make(array $attributes): self
    {
        return new self($attributes);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
