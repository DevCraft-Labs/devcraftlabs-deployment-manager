<?php

use App\Contracts\Services\DeploymentServiceInterface;
use App\Models\DeploymentScript;
use Cron\CronExpression;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function (): void {
    DeploymentScript::query()
        ->where('active', true)
        ->where('cron_enabled', true)
        ->whereNotNull('cron_expression')
        ->get()
        ->each(function (DeploymentScript $script): void {
            if (CronExpression::factory($script->cron_expression)->isDue()) {
                app(DeploymentServiceInterface::class)->queue($script, null, 'cron');
            }
        });
})->everyMinute()->name('deployment-cron-dispatcher');
