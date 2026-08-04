<?php

namespace App\Services;

use App\Contracts\Services\RedisConnectionServiceInterface;
use App\DTOs\RedisTestResultData;
use App\Models\ConnectionTestHistory;
use App\Models\RedisProfile;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Predis\Client as PredisClient;
use Throwable;

class RedisConnectionService implements RedisConnectionServiceInterface
{
    public function testConnection(RedisProfile $profile, ?User $user, ?int $ttl = null): RedisTestResultData
    {
        $ttl = max(1, $ttl ?? $profile->default_ttl ?? 300);
        $key = 'dcl:health:' . uniqid('', true);
        $value = bin2hex(random_bytes(12));
        $start = microtime(true);

        try {
            $client = new PredisClient([
                'scheme' => $profile->tls ? 'tls' : 'tcp',
                'host' => $profile->host,
                'port' => $profile->port,
                'username' => $profile->username,
                'password' => $profile->password,
                'database' => $profile->database,
                'timeout' => $profile->timeout,
            ]);

            $client->setex($key, $ttl, $value);
            $fetched = (string) $client->get($key);
            $ttlRead = (int) $client->ttl($key);
            $client->del([$key]);

            if ($fetched !== $value || $ttlRead < 0) {
                throw new \RuntimeException('Redis verification failed for SET/GET/TTL sequence.');
            }

            $latency = (int) round((microtime(true) - $start) * 1000);
            $response = 'SET/GET/TTL/DELETE verified';

            ConnectionTestHistory::query()->create([
                'connection_type' => 'redis',
                'connection_id' => $profile->id,
                'is_success' => true,
                'latency_ms' => $latency,
                'ttl' => $ttlRead,
                'response' => $response,
                'tested_by_user_id' => $user?->id,
                'meta' => ['key' => $key],
            ]);

            return new RedisTestResultData(true, $latency, $ttlRead, $response);
        } catch (Throwable $e) {
            Log::channel('redis')->error('Redis test failed', ['profile_id' => $profile->id, 'error' => $e->getMessage()]);

            ConnectionTestHistory::query()->create([
                'connection_type' => 'redis',
                'connection_id' => $profile->id,
                'is_success' => false,
                'response' => $e->getMessage(),
                'tested_by_user_id' => $user?->id,
            ]);

            return new RedisTestResultData(false, null, null, $e->getMessage());
        }
    }
}
