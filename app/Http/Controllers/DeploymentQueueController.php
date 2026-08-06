<?php

namespace App\Http\Controllers;

use App\Models\DeploymentExecution;
use Illuminate\View\View;

class DeploymentQueueController extends Controller
{
    public function index(): View
    {
        return view('deployments.queue', [
            'executions' => DeploymentExecution::query()
                ->with(['script', 'user'])
                ->whereIn('status', ['queued', 'running'])
                ->orderByRaw("CASE status WHEN 'running' THEN 0 ELSE 1 END")
                ->oldest('started_at')
                ->paginate(25),
        ]);
    }
}
