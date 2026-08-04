<?php

namespace App\Services;

use App\Contracts\Services\SmtpServiceInterface;
use App\Models\ConnectionTestHistory;
use App\Models\SmtpProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SmtpService implements SmtpServiceInterface
{
    public function sendTestEmail(SmtpProfile $profile, string $recipientEmail): void
    {
        config()->set('mail.mailers.runtime', [
            'transport' => 'smtp',
            'host' => $profile->host,
            'port' => $profile->port,
            'encryption' => $profile->encryption,
            'username' => $profile->username,
            'password' => $profile->password,
            'timeout' => 20,
        ]);

        Mail::mailer('runtime')->raw('DevCraft Labs SMTP test was successful.', function ($mail) use ($profile, $recipientEmail): void {
            $mail->to($recipientEmail)
                ->from($profile->from_email, $profile->from_name)
                ->subject('SMTP Test - DevCraft Labs Deployment Manager');
        });

        ConnectionTestHistory::query()->create([
            'connection_type' => 'smtp',
            'connection_id' => $profile->id,
            'is_success' => true,
            'response' => 'Test email accepted for delivery.',
            'tested_by_user_id' => Auth::id(),
        ]);

        Log::channel('smtp')->info('SMTP test sent', ['profile_id' => $profile->id, 'recipient' => $recipientEmail]);
    }
}
