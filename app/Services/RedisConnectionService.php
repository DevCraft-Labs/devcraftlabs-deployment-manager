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
    private function client(RedisProfile $profile): PredisClient
    {
        return new PredisClient([
            'scheme' => $profile->tls ? 'tls' : 'tcp',
            'host' => $profile->host,
            'port' => $profile->port,
            'username' => $profile->username,
            'password' => $profile->password,
            'database' => $profile->database,
            'timeout' => $profile->timeout,
        ]);
    }

    public function scanKeys(RedisProfile $profile, ?string $search = null, int $limit = 2000): array
    {
        $client = $this->client($profile);
        $pattern = $search !== null && $search !== '' ? '*' . $search . '*' : '*';

        $keys = [];
        $cursor = 0;

        do {
            [$cursor, $batch] = $client->scan($cursor, ['match' => $pattern, 'count' => 500]);
            foreach ($batch as $key) {
                $keys[] = $key;
                if (count($keys) >= $limit) {
                    break;
                }
            }
        } while ((int) $cursor !== 0 && count($keys) < $limit);

        $truncated = (int) $cursor !== 0 && count($keys) >= $limit;

        sort($keys);

        return ['keys' => $keys, 'truncated' => $truncated];
    }

    public function getKeyDetails(RedisProfile $profile, string $key): array
    {
        $client = $this->client($profile);

        if (!$client->exists($key)) {
            throw new \RuntimeException("Key \"{$key}\" does not exist.");
        }

        $type = (string) $client->type($key);
        $ttl = (int) $client->ttl($key);

        $value = match ($type) {
            'string' => $client->get($key),
            // Cast to object so json_encode() keeps purely-numeric field/member
            // names as object keys instead of silently reindexing them into a
            // JSON array (which would lose the real field names on save).
            'hash' => (object) $client->hgetall($key),
            'list' => $client->lrange($key, 0, -1),
            'set' => $client->smembers($key),
            'zset' => (object) $this->zsetWithScores($client, $key),
            default => null,
        };

        return [
            'key' => $key,
            'type' => $type,
            'ttl' => $ttl,
            'editable' => in_array($type, ['string', 'hash', 'list', 'set', 'zset'], true),
            'value' => $value,
        ];
    }

    private function zsetWithScores(PredisClient $client, string $key): array
    {
        $raw = $client->zrange($key, 0, -1, ['withscores' => true]);
        $result = [];
        foreach ($raw as $member => $score) {
            $result[$member] = $score;
        }

        return $result;
    }

    public function updateKeyValue(RedisProfile $profile, string $key, string $type, mixed $value, ?int $ttl): void
    {
        $client = $this->client($profile);

        if (!$client->exists($key)) {
            throw new \RuntimeException("Key \"{$key}\" does not exist.");
        }

        $existingType = (string) $client->type($key);
        if ($existingType !== $type) {
            throw new \RuntimeException("Key type mismatch: expected {$existingType}, got {$type}.");
        }

        // Preserve the key's current expiry across the delete+recreate below
        // unless the caller explicitly supplied a new TTL: otherwise every
        // save would silently strip expiry from keys that had one.
        $existingTtl = (int) $client->ttl($key);

        $client->transaction(function ($tx) use ($key, $type, $value): void {
            $tx->del([$key]);

            match ($type) {
                'string' => $tx->set($key, (string) $value),
                'hash' => is_array($value) && $value !== [] ? $tx->hmset($key, $value) : null,
                'list' => is_array($value) && $value !== [] ? $tx->rpush($key, array_values($value)) : null,
                'set' => is_array($value) && $value !== [] ? $tx->sadd($key, array_values($value)) : null,
                'zset' => is_array($value) && $value !== [] ? $tx->zadd($key, $value) : null,
                default => throw new \RuntimeException("Editing type \"{$type}\" is not supported."),
            };
        });

        if ($ttl !== null && $ttl > 0) {
            $client->expire($key, $ttl);
        } elseif ($existingTtl > 0) {
            $client->expire($key, $existingTtl);
        }
    }

    public function deleteKey(RedisProfile $profile, string $key): void
    {
        $this->client($profile)->del([$key]);
    }

    public function testConnection(RedisProfile $profile, ?User $user, ?int $ttl = null): RedisTestResultData
    {
        $ttl = max(1, $ttl ?? $profile->default_ttl ?? 300);
        $key = 'dcl:health:' . uniqid('', true);
        $value = bin2hex(random_bytes(12));
        $start = microtime(true);

        try {
            $client = $this->client($profile);

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
