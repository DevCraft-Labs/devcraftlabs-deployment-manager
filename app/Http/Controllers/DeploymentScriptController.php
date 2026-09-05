<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\DeploymentScriptRepositoryInterface;
use App\Contracts\Services\DeploymentServiceInterface;
use App\Http\Requests\DeploymentScript\UpdateDeploymentEnvironmentRequest;
use App\Http\Requests\DeploymentScript\StoreDeploymentScriptRequest;
use App\Http\Requests\DeploymentScript\UpdateDeploymentScriptRequest;
use App\Models\DeploymentScript;
use App\Models\RedisProfile;
use App\Models\SmtpProfile;
use App\Models\TelegramConnection;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class DeploymentScriptController extends Controller
{
    public function __construct(
        private readonly DeploymentScriptRepositoryInterface $repository,
        private readonly DeploymentServiceInterface $deploymentService,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $sort = $request->string('sort', 'created_at')->value();
        $direction = $request->string('direction', 'desc')->value();
        $scripts = $this->repository->paginate($search, $sort, $direction);
        $serviceStatuses = DeploymentScript::query()
            ->whereNotNull('health_check_url')
            ->where('health_check_url', '!=', '')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function (DeploymentScript $script): array {
            return [$script->id => $this->serviceStatus($script)];
        });

        return view('scripts.index', [
            'scripts' => $scripts,
            'search' => $search,
            'sort' => $sort,
            'direction' => $direction,
            'serviceStatuses' => $serviceStatuses,
            'configuredServiceCount' => $serviceStatuses->where('configured', true)->count(),
            'runningServiceCount' => $serviceStatuses->where('isRunning', true)->count(),
            'stoppedServiceCount' => $serviceStatuses->where('configured', true)->where('isRunning', false)->count(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('scripts.create', [
            'redisProfiles' => RedisProfile::query()->where('status', true)->orderBy('name')->get(),
            'smtpProfiles' => SmtpProfile::query()->where('status', true)->orderBy('name')->get(),
            'telegramConnections' => TelegramConnection::query()->where('status', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDeploymentScriptRequest $request): RedirectResponse
    {
        $script = $this->repository->create($request->validated());
        $this->auditLogger->log('script.create', DeploymentScript::class, $script->id);

        return redirect()->route('deployment-scripts.index')->with('status', 'Script created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(DeploymentScript $deploymentScript): View
    {
        return view('scripts.show', [
            'script' => $deploymentScript->load(['executions' => fn ($q) => $q->latest('started_at')->limit(20)]),
            'logFiles' => $this->logFiles($deploymentScript),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DeploymentScript $deploymentScript): View
    {
        return view('scripts.edit', [
            'script' => $deploymentScript,
            'redisProfiles' => RedisProfile::query()->where('status', true)->orderBy('name')->get(),
            'smtpProfiles' => SmtpProfile::query()->where('status', true)->orderBy('name')->get(),
            'telegramConnections' => TelegramConnection::query()->where('status', true)->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDeploymentScriptRequest $request, DeploymentScript $deploymentScript): RedirectResponse
    {
        $this->repository->update($deploymentScript, $request->validated());
        $this->auditLogger->log('script.update', DeploymentScript::class, $deploymentScript->id);

        return redirect()->route('deployment-scripts.index')->with('status', 'Script updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeploymentScript $deploymentScript): RedirectResponse
    {
        $this->repository->delete($deploymentScript);
        $this->auditLogger->log('script.delete', DeploymentScript::class, $deploymentScript->id);

        return redirect()->route('deployment-scripts.index')->with('status', 'Script deleted successfully.');
    }

    public function run(DeploymentScript $deploymentScript): RedirectResponse
    {
        $this->deploymentService->queue($deploymentScript, Auth::user(), 'manual');
        $this->auditLogger->log('script.run', DeploymentScript::class, $deploymentScript->id);

        return back()->with('status', "Deployment of {$deploymentScript->name} to {$deploymentScript->working_directory} has been queued.");
    }

    public function duplicate(DeploymentScript $deploymentScript): RedirectResponse
    {
        $copy = $deploymentScript->replicate();
        $copy->name = $deploymentScript->name . '-' . Str::random(5);
        $copy->save();

        $this->auditLogger->log('script.duplicate', DeploymentScript::class, $copy->id);

        return redirect()->route('deployment-scripts.edit', $copy)->with('status', 'Script duplicated.');
    }

    public function toggle(DeploymentScript $deploymentScript): RedirectResponse
    {
        $deploymentScript->update(['active' => !$deploymentScript->active]);
        $this->auditLogger->log('script.toggle', DeploymentScript::class, $deploymentScript->id, ['active' => $deploymentScript->active]);

        return back()->with('status', 'Script state updated.');
    }

    public function downloadApplicationLogs(DeploymentScript $deploymentScript): BinaryFileResponse
    {
        $realDirectory = $this->logDirectory($deploymentScript);

        $archivePath = storage_path('app/deployment-logs-' . $deploymentScript->id . '-' . now()->format('YmdHis') . '.zip');
        $archive = new \ZipArchive();
        abort_unless($archive->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true, 500, 'Unable to prepare application log archive.');

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($realDirectory, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile() && $file->isReadable()) {
                $archive->addFile($file->getPathname(), ltrim(str_replace($realDirectory, '', $file->getPathname()), DIRECTORY_SEPARATOR));
            }
        }

        $archive->close();

        return response()->download($archivePath, $deploymentScript->name . '-application-logs.zip')->deleteFileAfterSend();
    }

    public function downloadLogFile(DeploymentScript $deploymentScript, Request $request): BinaryFileResponse
    {
        $logFile = $this->logFile($deploymentScript, (string) $request->query('file'));

        return response()->download($logFile);
    }

    public function tailLogFile(DeploymentScript $deploymentScript, Request $request): \Illuminate\Http\Response
    {
        $logFile = $this->logFile($deploymentScript, (string) $request->query('file'));
        $contents = file_get_contents($logFile);
        abort_if($contents === false, 500, 'Unable to read the requested log file.');

        $lines = preg_split('/\R/', $contents) ?: [];
        $tail = implode("\n", array_slice($lines, -200));

        return response($tail, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function editEnvironment(DeploymentScript $deploymentScript): View
    {
        return view('scripts.environment', [
            'script' => $deploymentScript,
            'contents' => $this->environmentContents($deploymentScript),
        ]);
    }

    public function updateEnvironment(UpdateDeploymentEnvironmentRequest $request, DeploymentScript $deploymentScript): RedirectResponse
    {
        $environmentPath = $this->environmentPath($deploymentScript, true);

        abort_if(is_link($environmentPath), 403, 'Symbolic link environment files cannot be edited.');
        abort_unless(file_put_contents($environmentPath, $request->validated('contents'), LOCK_EX) !== false, 500, 'Unable to save the environment file.');

        $this->auditLogger->log('script.environment.update', DeploymentScript::class, $deploymentScript->id);

        return redirect()->route('deployment-scripts.environment.edit', $deploymentScript)
            ->with('status', 'Environment file saved.');
    }

    private function environmentContents(DeploymentScript $deploymentScript): string
    {
        $environmentPath = $this->environmentPath($deploymentScript);

        abort_if(is_link($environmentPath), 403, 'Symbolic link environment files cannot be edited.');

        if (!is_file($environmentPath)) {
            return '';
        }

        $contents = file_get_contents($environmentPath);
        abort_if($contents === false, 500, 'Unable to read the environment file.');

        return $contents;
    }

    private function environmentPath(DeploymentScript $deploymentScript, bool $requireWritable = false): string
    {
        $directory = realpath($deploymentScript->working_directory);

        abort_unless($directory !== false && is_dir($directory) && is_readable($directory), 404, 'Configured project directory is unavailable.');
        abort_if($requireWritable && !is_writable($directory), 403, 'Configured project directory is not writable.');

        return $directory . DIRECTORY_SEPARATOR . '.env';
    }

    private function logDirectory(DeploymentScript $deploymentScript): string
    {
        $logDirectory = $deploymentScript->log_directory ?: rtrim($deploymentScript->working_directory, '/\\') . '/storage/logs';
        $realDirectory = realpath($logDirectory);

        abort_unless($realDirectory && is_dir($realDirectory) && is_readable($realDirectory), 404, 'Configured application log directory is unavailable.');

        return $realDirectory;
    }

    private function logFiles(DeploymentScript $deploymentScript): array
    {
        $logDirectory = $deploymentScript->log_directory ?: rtrim($deploymentScript->working_directory, '/\\') . '/storage/logs';
        $directory = realpath($logDirectory);

        if ($directory === false || !is_dir($directory) || !is_readable($directory)) {
            return [];
        }

        $files = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if (!$file->isFile() || $file->isLink() || !$file->isReadable()) {
                continue;
            }

            $path = $file->getPathname();
            $files[] = [
                'name' => ltrim(str_replace($directory, '', $path), DIRECTORY_SEPARATOR),
                'updatedAt' => \Carbon\Carbon::createFromTimestamp($file->getMTime()),
                'size' => $file->getSize(),
            ];
        }

        usort($files, fn (array $left, array $right) => $right['updatedAt'] <=> $left['updatedAt']);

        return $files;
    }

    private function logFile(DeploymentScript $deploymentScript, string $relativePath): string
    {
        abort_if($relativePath === '' || str_contains($relativePath, "\0") || preg_match('#(^|[\\\\/])\.\.([\\\\/]|$)#', $relativePath), 404);

        $directory = $this->logDirectory($deploymentScript);
        $path = realpath($directory . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath));

        abort_unless($path && str_starts_with($path, $directory . DIRECTORY_SEPARATOR) && is_file($path) && !is_link($path) && is_readable($path), 404, 'Requested log file is unavailable.');

        return $path;
    }

    private function serviceStatus(DeploymentScript $script): array
    {
        if (blank($script->health_check_url)) {
            return ['configured' => false, 'isRunning' => false, 'status' => null, 'responseTime' => null, 'error' => null];
        }

        try {
            $startedAt = microtime(true);
            $response = Http::timeout(8)->get($script->health_check_url);
            $responseTime = (int) round((microtime(true) - $startedAt) * 1000);
            $isRunning = $response->status() >= 200 && $response->status() < 400;

            return compact('isRunning', 'responseTime') + ['configured' => true, 'status' => $response->status(), 'error' => null];
        } catch (Throwable $exception) {
            return ['configured' => true, 'isRunning' => false, 'status' => null, 'responseTime' => null, 'error' => $exception->getMessage()];
        }
    }
}
