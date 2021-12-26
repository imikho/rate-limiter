<?php

namespace App\Struct;

class RateLimiterConfig implements \JsonSerializable
{
    public int $requestCount;

    /** @var float $intervalLength - length of interval in seconds */
    public float $intervalLength;

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function __construct(int $requestCount, float $intervalLength)
    {
        $this->requestCount = $requestCount;
        $this->intervalLength = $intervalLength;
    }

    public function getIntervalLengthInMicroSeconds(): float
    {
        return $this->intervalLength * 1000000;
    }

    public static function createFromArray(array $data): self
    {
        return new static(
            $data['request_count'],
            $data['interval_length']
        );
    }

    public function toArray(): array
    {
        return [
            'request_count' => $this->requestCount,
            'interval_length' => $this->intervalLength,
        ];
    }
}
