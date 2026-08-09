<?php

return [

    /*
    |--------------------------------------------------------------------------
    | File Clipboard
    |--------------------------------------------------------------------------
    |
    | Settings for the temporary file clipboard: uploaded files are stored on
    | disk while only metadata (name, size, path, expiry) lives in Redis.
    |
    */

    'file_disk' => env('CLIPBOARD_FILE_DISK', 'local'),

    'file_directory' => 'clipboard-files',

    'max_file_size_kb' => (int) env('CLIPBOARD_MAX_FILE_SIZE_KB', 10240), // 10MB

    'file_ttl_seconds' => (int) env('CLIPBOARD_FILE_TTL_SECONDS', 300), // 5 minutes

];
