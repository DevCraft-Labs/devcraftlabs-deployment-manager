<?php

namespace App\Services;

use App\Contracts\Services\TemporaryClipboardServiceInterface;
use Illuminate\Support\Facades\Redis;

class TemporaryClipboardService implements TemporaryClipboardServiceInterface
{
    private const TTL_SECONDS = 300;

    private const KEY_PREFIX = 'temporary-clipboard:';

    public function create(string $content): string
    {
        $identifier = bin2hex(random_bytes(16));

        Redis::setex($this->key($identifier), self::TTL_SECONDS, json_encode([
            'content' => $content,
            'created_at' => now()->toIso8601String(),
        ], JSON_THROW_ON_ERROR));

        return $identifier;
    }

    public function find(string $identifier): ?array
    {
        $clipboard = Redis::get($this->key($identifier));

        if (!is_string($clipboard)) {
            return null;
        }

        $data = json_decode($clipboard, true, 512, JSON_THROW_ON_ERROR);
        $ttl = Redis::ttl($this->key($identifier));

        return [
            'content' => $data['content'],
            'created_at' => $data['created_at'],
            'expires_in' => max(0, $ttl),
        ];
    }

    public function update(string $identifier, string $content): bool
    {
        if (!$this->find($identifier)) {
            return false;
        }

        Redis::setex($this->key($identifier), self::TTL_SECONDS, json_encode([
            'content' => $content,
            'created_at' => now()->toIso8601String(),
        ], JSON_THROW_ON_ERROR));

        return true;
    }

    public function delete(string $identifier): bool
    {
        return Redis::del($this->key($identifier)) > 0;
    }

    private function key(string $identifier): string
    {
        return self::KEY_PREFIX . $identifier;
    }
}