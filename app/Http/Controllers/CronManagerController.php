<?php

namespace App\Http\Controllers;

use App\Models\DeploymentScript;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CronManagerController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function index(): View
    {
        return view('cron.index', [
            'scripts' => DeploymentScript::query()->whereNotNull('cron_expression')->latest()->paginate(15),
        ]);
    }

    public function update(Request $request, DeploymentScript $deploymentScript): RedirectResponse
    {
        $data = $request->validate([
            'cron_enabled' => ['required', 'boolean'],
            'cron_expression' => ['required', 'string', 'max:255'],
        ]);

        $deploymentScript->update($data);
        $this->auditLogger->log('cron.update', DeploymentScript::class, $deploymentScript->id, $data);

        return back()->with('status', 'Cron schedule updated.');
    }
}
