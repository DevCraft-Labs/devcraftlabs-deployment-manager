<?php

namespace App\Http\Controllers;

use App\Models\DeploymentExecution;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Throwable;

class DeploymentQueueController extends Controller
{
    public function index(): View
    {
        $queuedExecutions = DeploymentExecution::query()
            ->with(['script', 'user'])
            ->whereIn('status', ['queued', 'running'])
            ->orderByRaw("CASE status WHEN 'running' THEN 0 ELSE 1 END")
            ->oldest('started_at')
            ->get();

        $historyExecutions = DeploymentExecution::query()
            ->with(['script', 'user'])
            ->whereNotIn('status', ['queued', 'running'])
            ->latest('finished_at')
            ->paginate(25, ['*'], 'history_page');

        $scripts = $queuedExecutions->pluck('script')
            ->merge($historyExecutions->getCollection()->pluck('script'))
            ->filter()
            ->unique('id');

        return view('deployments.queue', [
            'queuedExecutions' => $queuedExecutions,
            'historyExecutions' => $historyExecutions,
            'healthStatuses' => $this->healthStatuses($scripts),
        ]);
    }

    private function healthStatuses(iterable $scripts): array
    {
        $statuses = [];

        foreach ($scripts as $script) {
            if (!$script->health_check_url) {
                $statuses[$script->id] = ['state' => 'not-configured', 'label' => 'Not configured'];
                continue;
            }

            try {
                $response = Http::timeout(8)->get($script->health_check_url);
                $isRunning = $response->status() >= 200 && $response->status() < 400;
                $statuses[$script->id] = ['state' => $isRunning ? 'running' : 'stopped', 'label' => $isRunning ? 'Running (' . $response->status() . ')' : 'Stopped (' . $response->status() . ')'];
            } catch (Throwable) {
                $statuses[$script->id] = ['state' => 'stopped', 'label' => 'Stopped (unreachable)'];
            }
        }

        return $statuses;
    }
}
