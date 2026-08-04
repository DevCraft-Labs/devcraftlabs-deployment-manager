<?php

namespace App\Http\Controllers;

use App\Contracts\Services\DeploymentServiceInterface;
use App\Contracts\Services\TelegramServiceInterface;
use App\Models\DeploymentExecution;
use App\Models\DeploymentScript;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private readonly DeploymentServiceInterface $deploymentService,
        private readonly TelegramServiceInterface $telegramService,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $text = (string) data_get($request->all(), 'message.text', '');
        $chatId = (string) data_get($request->all(), 'message.chat.id', '');

        if ($text === '') {
            return response()->json(['ok' => true]);
        }

        if (str_starts_with($text, '/deploy')) {
            $parts = preg_split('/\s+/', trim($text));
            $scriptName = $parts[1] ?? null;

            if (!$scriptName) {
                return response()->json(['message' => 'Usage: /deploy app-name']);
            }

            $script = DeploymentScript::query()->where('name', $scriptName)->first();
            if (!$script) {
                return response()->json(['message' => 'Script not found']);
            }

            $this->deploymentService->queue($script, null, 'telegram');

            return response()->json(['message' => 'Deployment queued']);
        }

        if ($text === '/status') {
            $latest = DeploymentExecution::query()->latest('started_at')->first();

            return response()->json([
                'status' => $latest?->is_success ? 'success' : 'failed',
                'execution_id' => $latest?->id,
            ]);
        }

        if ($text === '/history') {
            return response()->json(DeploymentExecution::query()->latest('started_at')->limit(10)->get());
        }

        if ($text === '/report') {
            return response()->json(['message' => 'Use web UI report export endpoint for XLSX report generation.']);
        }

        return response()->json(['message' => '/deploy, /status, /history, /report, /help']);
    }
}
