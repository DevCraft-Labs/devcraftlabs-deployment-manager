<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class PruneExpiredFileClipboards extends Command
{
    protected $signature = 'clipboard:prune-files';

    protected $description = 'Delete file-clipboard uploads whose Redis metadata has expired (or is stale beyond a safety margin).';

    public function handle(): int
    {
        $disk = Storage::disk(config('clipboard.file_disk', 'local'));
        $directory = config('clipboard.file_directory', 'clipboard-files');
        $keyPrefix = 'temporary-clipboard-file:';

        // Files older than 2x the configured TTL are removed unconditionally,
        // as a safety net in case Redis was flushed/unreachable and metadata
        // for a still-referenced file was lost.
        $maxAgeSeconds = max(60, (int) config('clipboard.file_ttl_seconds', 300)) * 2;

        $pruned = 0;

        foreach ($disk->files($directory) as $path) {
            $identifier = basename($path);

            if (Redis::exists($keyPrefix . $identifier) > 0) {
                continue; // Still active per its Redis TTL.
            }

            // Metadata is gone (normal expiry, or Redis was flushed). Only
            // prune once the file is older than the safety margin, so a file
            // uploaded moments ago isn't deleted by a transient Redis blip
            // that happens before its metadata write lands.
            $lastModified = $disk->lastModified($path);
            $ageSeconds = $lastModified !== false ? (time() - $lastModified) : PHP_INT_MAX;

            if ($ageSeconds >= $maxAgeSeconds) {
                $disk->delete($path);
                $pruned++;
            }
        }

        $this->info("Pruned {$pruned} expired file-clipboard upload(s).");

        return self::SUCCESS;
    }
}
