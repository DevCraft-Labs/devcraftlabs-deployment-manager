<?php

namespace App\Contracts\Services;

use App\DTOs\RedisTestResultData;
use App\Models\RedisProfile;
use App\Models\User;

interface RedisConnectionServiceInterface
{
    public function testConnection(RedisProfile $profile, ?User $user, ?int $ttl = null): RedisTestResultData;

    /**
     * Scan for keys matching the given search term (substring match), capped at $limit.
     *
     * @return array{keys: array<int, string>, truncated: bool}
     */
    public function scanKeys(RedisProfile $profile, ?string $search = null, int $limit = 2000): array;

    /**
     * Fetch a key's type, TTL, and value in an editable representation.
     */
    public function getKeyDetails(RedisProfile $profile, string $key): array;

    /**
     * Overwrite a key's value (and optionally its TTL) based on its Redis type.
     */
    public function updateKeyValue(RedisProfile $profile, string $key, string $type, mixed $value, ?int $ttl): void;

    /**
     * Permanently delete a key.
     */
    public function deleteKey(RedisProfile $profile, string $key): void;
}
