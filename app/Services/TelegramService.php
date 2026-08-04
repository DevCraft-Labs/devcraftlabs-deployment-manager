<?php

namespace App\Services;

use App\Contracts\Services\TelegramServiceInterface;
use App\Models\ConnectionTestHistory;
use App\Models\TelegramConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TelegramService implements TelegramServiceInterface
{
    public function sendMessage(TelegramConnection $connection, string $message): array
    {
        $url = sprintf('https://api.telegram.org/bot%s/sendMessage', $connection->bot_token);

        $response = Http::timeout(15)->post($url, [
            'chat_id' => $connection->chat_id,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ]);

        Log::channel('telegram')->info('Telegram message sent', ['connection_id' => $connection->id, 'ok' => $response->ok()]);

        return $response->json() ?? ['ok' => false];
    }

    public function test(TelegramConnection $connection): array
    {
        $result = $this->sendMessage($connection, '*DevCraft Labs* Telegram test: success path reached.');

        ConnectionTestHistory::query()->create([
            'connection_type' => 'telegram',
            'connection_id' => $connection->id,
            'is_success' => (bool) ($result['ok'] ?? false),
            'response' => json_encode($result),
            'tested_by_user_id' => Auth::id(),
        ]);

        return $result;
    }
}
