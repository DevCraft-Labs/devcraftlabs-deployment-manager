<?php

use App\Http\Controllers\Api\DeploymentApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.token', 'throttle:60,1'])->group(function (): void {
    Route::post('/deploy/{script}', [DeploymentApiController::class, 'deploy']);
    Route::get('/deployment/history', [DeploymentApiController::class, 'history']);
    Route::get('/deployment/status', [DeploymentApiController::class, 'status']);
    Route::get('/deployment/report', [DeploymentApiController::class, 'history']);

    Route::post('/test/redis', [DeploymentApiController::class, 'testRedis']);
    Route::post('/test/smtp', [DeploymentApiController::class, 'testSmtp']);
    Route::post('/test/telegram', [DeploymentApiController::class, 'testTelegram']);

    Route::get('/scripts', [DeploymentApiController::class, 'scripts']);
    Route::get('/scripts/{script}', [DeploymentApiController::class, 'script']);
});
