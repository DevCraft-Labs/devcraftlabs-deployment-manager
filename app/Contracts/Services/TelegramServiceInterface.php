<?php

namespace App\Contracts\Services;

use App\Models\TelegramConnection;

interface TelegramServiceInterface
{
    public function sendMessage(TelegramConnection $connection, string $message): array;

    public function test(TelegramConnection $connection): array;
}
