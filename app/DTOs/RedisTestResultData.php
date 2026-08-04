<?php

namespace App\DTOs;

class RedisTestResultData
{
    public function __construct(
        public readonly bool $success,
        public readonly ?int $latencyMs,
        public readonly ?int $ttl,
        public readonly string $response,
    ) {
    }
}
