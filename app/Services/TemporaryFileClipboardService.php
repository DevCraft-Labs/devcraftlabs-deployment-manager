<?php

namespace App\Services;

use App\Contracts\Services\TemporaryFileClipboardServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class TemporaryFileClipboardService implements TemporaryFileClipboardServiceInterface
{
    private const KEY_PREFIX = 'temporary-clipboard-file:';

    public function create(UploadedFile $file): array
    {
        $identifier = bin2hex(random_bytes(16));
        $ttl = max(1, (int) config('clipboard.file_ttl_seconds', 300));

        $storedPath = $file->storeAs(
            config('clipboard.file_directory', 'clipboard-files'),
            $identifier,
            config('clipboard.file_disk', 'local'),
        );

        $meta = [
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
            'size' => (string) $file->getSize(),
            'stored_path' => $storedPath,
            'created_at' => now()->toIso8601String(),
        ];

        $key = $this->key($identifier);
        Redis::hmset($key, $meta);
        Redis::expire($key, $ttl);

        return array_merge(['identifier' => $identifier], $meta, ['expires_in' => $ttl]);
    }

    public function find(string $identifier): ?array
    {
        $key = $this->key($identifier);
        $meta = Redis::hgetall($key);

        if (empty($meta)) {
            return null;
        }

        $ttl = (int) Redis::ttl($key);

        return array_merge($meta, [
            'identifier' => $identifier,
            'size' => (int) ($meta['size'] ?? 0),
            'expires_in' => max(0, $ttl),
        ]);
    }

    public function resolvePath(string $identifier): ?string
    {
        $entry = $this->find($identifier);
        if (!$entry) {
            return null;
        }

        $disk = Storage::disk(config('clipboard.file_disk', 'local'));
        if (!$disk->exists($entry['stored_path'])) {
            return null;
        }

        return $entry['stored_path'];
    }

    public function delete(string $identifier): bool
    {
        $entry = $this->find($identifier);
        Redis::del($this->key($identifier));

        if ($entry) {
            Storage::disk(config('clipboard.file_disk', 'local'))->delete($entry['stored_path']);
        }

        return $entry !== null;
    }

    private function key(string $identifier): string
    {
        return self::KEY_PREFIX . $identifier;
    }
}
