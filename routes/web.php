<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClipboardController;
use App\Http\Controllers\CronManagerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatabaseProvisioningController;
use App\Http\Controllers\DeploymentRunController;
use App\Http\Controllers\DeploymentQueueController;
use App\Http\Controllers\DeploymentScriptController;
use App\Http\Controllers\FileClipboardController;
use App\Http\Controllers\RedisProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ServerMonitoringController;
use App\Http\Controllers\SmtpProfileController;
use App\Http\Controllers\TelegramConnectionController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])
    ->middleware('telegram.secret')
    ->name('telegram.webhook');

Route::get('/clipboard', [ClipboardController::class, 'create'])->name('clipboard.create');
Route::post('/clipboard', [ClipboardController::class, 'store'])->middleware('throttle:20,1')->name('clipboard.store');
Route::get('/clipboard/{clipboard}', [ClipboardController::class, 'show'])->where('clipboard', '[a-f0-9]{32}')->name('clipboard.show');
Route::put('/clipboard/{clipboard}', [ClipboardController::class, 'update'])->middleware('throttle:30,1')->where('clipboard', '[a-f0-9]{32}')->name('clipboard.update');
Route::delete('/clipboard/{clipboard}', [ClipboardController::class, 'destroy'])->middleware('throttle:30,1')->where('clipboard', '[a-f0-9]{32}')->name('clipboard.destroy');

Route::get('/file-clipboard', [FileClipboardController::class, 'create'])->name('file-clipboard.create');
Route::post('/file-clipboard', [FileClipboardController::class, 'store'])->middleware('throttle:5,1')->name('file-clipboard.store');
Route::get('/file-clipboard/{fileClipboard}', [FileClipboardController::class, 'show'])->where('fileClipboard', '[a-f0-9]{32}')->name('file-clipboard.show');
Route::get('/file-clipboard/{fileClipboard}/download', [FileClipboardController::class, 'download'])->middleware('throttle:5,1')->where('fileClipboard', '[a-f0-9]{32}')->name('file-clipboard.download');
Route::delete('/file-clipboard/{fileClipboard}', [FileClipboardController::class, 'destroy'])->middleware('throttle:5,1')->where('fileClipboard', '[a-f0-9]{32}')->name('file-clipboard.destroy');

Route::middleware('auth')->group(function (): void {
    Route::get('/', DashboardController::class)->middleware('can:dashboard.view')->name('dashboard');
    Route::get('/server-monitoring', [ServerMonitoringController::class, 'index'])->middleware('can:deployments.view')->name('monitoring.index');

    Route::resource('redis-profiles', RedisProfileController::class)->only(['create', 'store'])->middleware('can:connections.create');
    Route::resource('redis-profiles', RedisProfileController::class)->only(['index', 'show'])->middleware('can:connections.view');
    Route::resource('redis-profiles', RedisProfileController::class)->only(['edit', 'update'])->middleware('can:connections.update');
    Route::delete('/redis-profiles/{redisProfile}', [RedisProfileController::class, 'destroy'])->middleware('can:connections.delete')->name('redis-profiles.destroy');
    Route::post('/redis-profiles/{redisProfile}/test', [RedisProfileController::class, 'test'])->middleware('can:connections.test')->name('redis-profiles.test');
    Route::get('/redis-profiles/{redisProfile}/keys', [RedisProfileController::class, 'keys'])->middleware('can:connections.view')->name('redis-profiles.keys');
    Route::get('/redis-profiles/{redisProfile}/keys/data', [RedisProfileController::class, 'keysData'])->middleware('can:connections.view')->name('redis-profiles.keys.data');
    Route::get('/redis-profiles/{redisProfile}/keys/value', [RedisProfileController::class, 'keyShow'])->middleware('can:connections.view')->name('redis-profiles.keys.show');
    Route::put('/redis-profiles/{redisProfile}/keys/value', [RedisProfileController::class, 'keyUpdate'])->middleware('can:connections.update')->name('redis-profiles.keys.update');
    Route::delete('/redis-profiles/{redisProfile}/keys/value', [RedisProfileController::class, 'keyDestroy'])->middleware('can:connections.delete')->name('redis-profiles.keys.destroy');

    Route::resource('smtp-profiles', SmtpProfileController::class)->only(['create', 'store'])->middleware('can:connections.create');
    Route::resource('smtp-profiles', SmtpProfileController::class)->only(['index', 'show'])->middleware('can:connections.view');
    Route::resource('smtp-profiles', SmtpProfileController::class)->only(['edit', 'update'])->middleware('can:connections.update');
    Route::delete('/smtp-profiles/{smtpProfile}', [SmtpProfileController::class, 'destroy'])->middleware('can:connections.delete')->name('smtp-profiles.destroy');
    Route::post('/smtp-profiles/{smtpProfile}/test', [SmtpProfileController::class, 'test'])->middleware('can:connections.test')->name('smtp-profiles.test');

    Route::resource('telegram-connections', TelegramConnectionController::class)->only(['create', 'store'])->middleware('can:connections.create');
    Route::resource('telegram-connections', TelegramConnectionController::class)->only(['index', 'show'])->middleware('can:connections.view');
    Route::resource('telegram-connections', TelegramConnectionController::class)->only(['edit', 'update'])->middleware('can:connections.update');
    Route::delete('/telegram-connections/{telegramConnection}', [TelegramConnectionController::class, 'destroy'])->middleware('can:connections.delete')->name('telegram-connections.destroy');
    Route::post('/telegram-connections/{telegramConnection}/test', [TelegramConnectionController::class, 'test'])->middleware('can:connections.test')->name('telegram-connections.test');

    Route::resource('deployment-scripts', DeploymentScriptController::class)->only(['create', 'store'])->middleware('can:scripts.create');
    Route::resource('deployment-scripts', DeploymentScriptController::class)->only(['index', 'show'])->middleware('can:scripts.view');
    Route::resource('deployment-scripts', DeploymentScriptController::class)->only(['edit', 'update'])->middleware('can:scripts.update');
    Route::delete('/deployment-scripts/{deploymentScript}', [DeploymentScriptController::class, 'destroy'])->middleware('can:scripts.delete')->name('deployment-scripts.destroy');
    Route::post('/deployment-scripts/{deploymentScript}/run', [DeploymentScriptController::class, 'run'])->middleware('can:scripts.run')->name('deployment-scripts.run');
    Route::post('/deployment-scripts/{deploymentScript}/duplicate', [DeploymentScriptController::class, 'duplicate'])->middleware('can:scripts.create')->name('deployment-scripts.duplicate');
    Route::post('/deployment-scripts/{deploymentScript}/toggle', [DeploymentScriptController::class, 'toggle'])->middleware('can:scripts.update')->name('deployment-scripts.toggle');
    Route::get('/deployment-scripts/{deploymentScript}/application-logs', [DeploymentScriptController::class, 'downloadApplicationLogs'])->middleware('can:deployments.view')->name('deployment-scripts.application-logs');
    Route::get('/deployment-scripts/{deploymentScript}/environment', [DeploymentScriptController::class, 'editEnvironment'])->middleware('can:scripts.update')->name('deployment-scripts.environment.edit');
    Route::put('/deployment-scripts/{deploymentScript}/environment', [DeploymentScriptController::class, 'updateEnvironment'])->middleware('can:scripts.update')->name('deployment-scripts.environment.update');

    Route::get('/deployments/{execution}/logs', [DeploymentRunController::class, 'logs'])->middleware('can:deployments.view')->name('deployments.logs');
    Route::get('/deployment-queue', [DeploymentQueueController::class, 'index'])->middleware('can:deployments.view')->name('deployments.queue');

    Route::get('/cron', [CronManagerController::class, 'index'])->middleware('can:cron.view')->name('cron.index');
    Route::patch('/cron/{deploymentScript}', [CronManagerController::class, 'update'])->middleware('can:cron.update')->name('cron.update');

    Route::get('/provisioning', [DatabaseProvisioningController::class, 'index'])->middleware('can:provisioning.view')->name('provisioning.index');
    Route::get('/provisioning/create', [DatabaseProvisioningController::class, 'create'])->middleware('can:provisioning.create')->name('provisioning.create');
    Route::post('/provisioning', [DatabaseProvisioningController::class, 'store'])->middleware('can:provisioning.create')->name('provisioning.store');
    Route::get('/provisioning/{connection}/edit', [DatabaseProvisioningController::class, 'edit'])->middleware('can:provisioning.update')->name('provisioning.edit');
    Route::put('/provisioning/{connection}', [DatabaseProvisioningController::class, 'update'])->middleware('can:provisioning.update')->name('provisioning.update');
    Route::delete('/provisioning/{connection}', [DatabaseProvisioningController::class, 'destroy'])->middleware('can:provisioning.delete')->name('provisioning.destroy');
    Route::post('/provisioning/{connection}/test', [DatabaseProvisioningController::class, 'test'])->middleware('can:provisioning.create')->name('provisioning.test');
    Route::get('/provisioning/{connection}', [DatabaseProvisioningController::class, 'browse'])->middleware('can:provisioning.view')->name('provisioning.browse');
    Route::post('/provisioning/{connection}/rows', [DatabaseProvisioningController::class, 'storeRow'])->middleware('can:provisioning.data.create')->name('provisioning.rows.store');
    Route::put('/provisioning/{connection}/rows', [DatabaseProvisioningController::class, 'updateRow'])->middleware('can:provisioning.data.update')->name('provisioning.rows.update');
    Route::delete('/provisioning/{connection}/rows', [DatabaseProvisioningController::class, 'destroyRow'])->middleware('can:provisioning.data.delete')->name('provisioning.rows.destroy');

    Route::get('/reports/download', [ReportController::class, 'download'])->middleware('can:reports.export')->name('reports.download');
    Route::post('/reports/queue', [ReportController::class, 'queue'])->middleware('can:reports.export')->name('reports.queue');

    Route::get('/settings', [SettingsController::class, 'index'])->middleware('can:settings.view')->name('settings.index');
    Route::patch('/settings', [SettingsController::class, 'update'])->middleware('can:settings.manage')->name('settings.update');

    Route::resource('users', UserController::class)->except(['show'])->middleware('can:users.manage');
});
