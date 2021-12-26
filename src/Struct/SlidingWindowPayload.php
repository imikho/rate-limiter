<?php

namespace App\Struct;

use DateTime;

final class SlidingWindowPayload implements \JsonSerializable
{
    public float $timestamp;
    public int $previousCount;
    public int $currentCount;

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public static function createInitial(int $previousCount): self
    {
        $i = new self();

        $i->timestamp = microtime(true);
        $i->previousCount = $previousCount;
        $i->currentCount = 0;

        return $i;
    }

    public function toArray(): array
    {
        return [
            'timestamp' => $this->timestamp,
            'previous_count' => $this->previousCount,
            'current_count' => $this->currentCount,
        ];
    }

    public static function createFromArray(array $data): self
    {
        $i = new self();

        $i->timestamp = $data['timestamp'];
        $i->previousCount = $data['previous_count'];
        $i->currentCount = $data['current_count'];

        return $i;
    }
}
