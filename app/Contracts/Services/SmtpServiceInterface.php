<?php

namespace App\Contracts\Services;

use App\Models\SmtpProfile;

interface SmtpServiceInterface
{
    public function sendTestEmail(SmtpProfile $profile, string $recipientEmail): void;
}
