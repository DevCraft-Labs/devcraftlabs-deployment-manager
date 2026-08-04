<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CronManagerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatabaseProvisioningController;
use App\Http\Controllers\DeploymentRunController;
use App\Http\Controllers\DeploymentScriptController;
use App\Http\Controllers\RedisProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SmtpProfileController;
use App\Http\Controllers\TelegramConnectionController;
use App\Http\Controllers\TelegramWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])
    ->middleware('telegram.secret')
    ->name('telegram.webhook');

Route::middleware('auth')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('redis-profiles', RedisProfileController::class);
    Route::post('/redis-profiles/{redisProfile}/test', [RedisProfileController::class, 'test'])->name('redis-profiles.test');

    Route::resource('smtp-profiles', SmtpProfileController::class);
    Route::post('/smtp-profiles/{smtpProfile}/test', [SmtpProfileController::class, 'test'])->name('smtp-profiles.test');

    Route::resource('telegram-connections', TelegramConnectionController::class);
    Route::post('/telegram-connections/{telegramConnection}/test', [TelegramConnectionController::class, 'test'])->name('telegram-connections.test');

    Route::resource('deployment-scripts', DeploymentScriptController::class);
    Route::post('/deployment-scripts/{deploymentScript}/run', [DeploymentScriptController::class, 'run'])->name('deployment-scripts.run');
    Route::post('/deployment-scripts/{deploymentScript}/duplicate', [DeploymentScriptController::class, 'duplicate'])->name('deployment-scripts.duplicate');
    Route::post('/deployment-scripts/{deploymentScript}/toggle', [DeploymentScriptController::class, 'toggle'])->name('deployment-scripts.toggle');

    Route::get('/deployments/{execution}/logs', [DeploymentRunController::class, 'logs'])->name('deployments.logs');

    Route::get('/cron', [CronManagerController::class, 'index'])->name('cron.index');
    Route::patch('/cron/{deploymentScript}', [CronManagerController::class, 'update'])->name('cron.update');

    Route::get('/provisioning', [DatabaseProvisioningController::class, 'index'])->name('provisioning.index');
    Route::get('/provisioning/{connection}', [DatabaseProvisioningController::class, 'browse'])->name('provisioning.browse');

    Route::get('/reports/download', [ReportController::class, 'download'])->name('reports.download');
    Route::post('/reports/queue', [ReportController::class, 'queue'])->name('reports.queue');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');
});
