<?php

namespace App\Http\Controllers;

use App\Models\ActivityAudit;
use App\Models\ApplicationSetting;
use App\Models\DeploymentExecution;
use App\Models\DeploymentScript;
use App\Models\RedisProfile;
use App\Models\SmtpProfile;
use App\Models\TelegramConnection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $lastDeployment = DeploymentExecution::query()->latest('started_at')->first();
        $lastFailed = DeploymentExecution::query()->where('is_success', false)->latest('started_at')->first();
        $lastSuccess = DeploymentExecution::query()->where('is_success', true)->latest('started_at')->first();
        $activeCron = DeploymentScript::query()->where('cron_enabled', true)->count();
        $activeDeploymentCount = DeploymentExecution::query()->whereIn('status', ['queued', 'running'])->count();

        return view('dashboard.index', [
            'lastDeployment' => $lastDeployment,
            'lastFailed' => $lastFailed,
            'lastSuccess' => $lastSuccess,
            'activeCronjobs' => $activeCron,
            'activeDeploymentCount' => $activeDeploymentCount,
            'totalScripts' => DeploymentScript::query()->count(),
            'totalTelegramConnections' => TelegramConnection::query()->count(),
            'totalSmtpConnections' => SmtpProfile::query()->count(),
            'totalRedisConnections' => RedisProfile::query()->count(),
            'recentActivities' => ActivityAudit::query()->latest()->limit(20)->get(),
            'announcementHtml' => ApplicationSetting::query()->value('announcement_html'),
        ]);
    }
}
