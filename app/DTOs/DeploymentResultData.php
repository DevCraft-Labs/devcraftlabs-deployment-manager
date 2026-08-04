<?php

namespace App\DTOs;

class DeploymentResultData
{
    public function __construct(
        public readonly int $executionId,
        public readonly bool $success,
        public readonly ?int $exitCode,
        public readonly ?int $durationMs,
        public readonly string $stdout,
        public readonly string $stderr,
    ) {
    }
}
