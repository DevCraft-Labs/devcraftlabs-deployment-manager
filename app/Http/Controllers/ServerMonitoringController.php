<?php

namespace App\Http\Controllers;

use App\Models\DeploymentScript;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class ServerMonitoringController extends Controller
{
    public function index(): View
    {
        $servers = DeploymentScript::query()
            ->whereNotNull('health_check_url')
            ->where('health_check_url', '!=', '')
            ->orderBy('name')
            ->get()
            ->map(function (DeploymentScript $script): array {
                try {
                    $startedAt = microtime(true);
                    $response = Http::timeout(8)->get($script->health_check_url);
                    $responseTime = (int) round((microtime(true) - $startedAt) * 1000);
                    $isRunning = $response->status() >= 200 && $response->status() < 400;

                    return compact('script', 'responseTime', 'isRunning') + ['status' => $response->status(), 'error' => null];
                } catch (Throwable $exception) {
                    return ['script' => $script, 'responseTime' => null, 'isRunning' => false, 'status' => null, 'error' => $exception->getMessage()];
                }
            });

        return view('monitoring.index', [
            'servers' => $servers,
            'runningCount' => $servers->where('isRunning', true)->count(),
            'stoppedCount' => $servers->where('isRunning', false)->count(),
        ]);
    }
}