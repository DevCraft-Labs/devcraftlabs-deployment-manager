<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\UpdateSettingsRequest;
use App\Models\ApplicationSetting;
use App\Models\RedisProfile;
use App\Models\SmtpProfile;
use App\Models\TelegramConnection;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function index(): View
    {
        return view('settings.index', [
            'setting' => ApplicationSetting::query()->firstOrCreate([]),
            'telegramConnections' => TelegramConnection::query()->orderBy('name')->get(),
            'smtpProfiles' => SmtpProfile::query()->orderBy('name')->get(),
            'redisProfiles' => RedisProfile::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $setting = ApplicationSetting::query()->firstOrCreate([]);
        $data = $request->validated();
        $data['announcement_html'] = $this->sanitizeAnnouncement($data['announcement_html'] ?? '');
        $setting->update($data);

        $this->auditLogger->log('settings.update', ApplicationSetting::class, $setting->id);

        return back()->with('status', 'Settings updated.');
    }

    private function sanitizeAnnouncement(string $announcement): ?string
    {
        $announcement = strip_tags($announcement, '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><blockquote><code><pre>');
        $announcement = preg_replace('/<([a-z0-9]+)\b[^>]*>/i', '<$1>', $announcement) ?? '';

        return filled(strip_tags($announcement)) ? $announcement : null;
    }
}
