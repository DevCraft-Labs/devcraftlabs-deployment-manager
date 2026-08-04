<?php

namespace App\Jobs;

use App\Contracts\Services\TelegramServiceInterface;
use App\Models\TelegramConnection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTelegramNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly int $telegramConnectionId, private readonly string $message)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(TelegramServiceInterface $telegramService): void
    {
        $connection = TelegramConnection::query()->find($this->telegramConnectionId);

        if ($connection) {
            $telegramService->sendMessage($connection, $this->message);
        }
    }
}
