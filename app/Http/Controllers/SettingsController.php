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
        $setting->update($request->validated());

        $this->auditLogger->log('settings.update', ApplicationSetting::class, $setting->id);

        return back()->with('status', 'Settings updated.');
    }
}
