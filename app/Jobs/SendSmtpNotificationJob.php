<?php

namespace App\Jobs;

use App\Contracts\Services\SmtpServiceInterface;
use App\Models\SmtpProfile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendSmtpNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly int $smtpProfileId, private readonly string $recipient)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(SmtpServiceInterface $smtpService): void
    {
        $profile = SmtpProfile::query()->find($this->smtpProfileId);

        if ($profile) {
            $smtpService->sendTestEmail($profile, $this->recipient);
        }
    }
}
