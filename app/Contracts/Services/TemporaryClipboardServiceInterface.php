<?php

namespace App\Contracts\Services;

interface TemporaryClipboardServiceInterface
{
    public function create(string $content): string;

    public function find(string $identifier): ?array;

    public function update(string $identifier, string $content): bool;

    public function delete(string $identifier): bool;
}