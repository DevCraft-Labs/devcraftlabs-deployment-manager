<?php

namespace App\Contracts\Services;

use Illuminate\Http\UploadedFile;

interface TemporaryFileClipboardServiceInterface
{
    /**
     * Store the uploaded file on disk and its metadata (with TTL) in Redis.
     */
    public function create(UploadedFile $file): array;

    /**
     * Fetch a single entry's metadata, or null if it has expired/doesn't exist.
     */
    public function find(string $identifier): ?array;

    /**
     * Resolve the absolute disk path for a still-active entry, or null.
     */
    public function resolvePath(string $identifier): ?string;

    /**
     * Delete an entry's metadata and its underlying file.
     */
    public function delete(string $identifier): bool;
}
