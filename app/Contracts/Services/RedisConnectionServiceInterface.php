<?php

namespace App\Contracts\Services;

use App\DTOs\RedisTestResultData;
use App\Models\RedisProfile;
use App\Models\User;

interface RedisConnectionServiceInterface
{
    public function testConnection(RedisProfile $profile, ?User $user, ?int $ttl = null): RedisTestResultData;
}
