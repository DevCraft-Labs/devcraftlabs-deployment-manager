<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\DeploymentExecutionRepositoryInterface;
use App\Contracts\Services\DeploymentServiceInterface;
use App\Contracts\Services\RedisConnectionServiceInterface;
use App\Contracts\Services\SmtpServiceInterface;
use App\Contracts\Services\TelegramServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\RunDeployRequest;
use App\Http\Requests\Api\TestRedisRequest;
use App\Http\Requests\Api\TestSmtpRequest;
use App\Http\Requests\Api\TestTelegramRequest;
use App\Models\DeploymentScript;
use App\Models\RedisProfile;
use App\Models\SmtpProfile;
use App\Models\TelegramConnection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DeploymentApiController extends Controller
{
    public function __construct(
        private readonly DeploymentServiceInterface $deploymentService,
        private readonly DeploymentExecutionRepositoryInterface $executionRepository,
        private readonly RedisConnectionServiceInterface $redisConnectionService,
        private readonly SmtpServiceInterface $smtpService,
        private readonly TelegramServiceInterface $telegramService,
    ) {
    }

    public function deploy(RunDeployRequest $request, DeploymentScript $script): JsonResponse
    {
        $this->deploymentService->queue($script, Auth::user(), $request->input('triggered_via', 'api'));

        return response()->json(['message' => 'Deployment queued.']);
    }

    public function history(): JsonResponse
    {
        return response()->json($this->executionRepository->latest(25));
    }

    public function status(): JsonResponse
    {
        $latest = $this->executionRepository->latest(1)->items()[0] ?? null;

        return response()->json(['latest' => $latest]);
    }

    public function scripts(): JsonResponse
    {
        return response()->json(DeploymentScript::query()->latest()->get());
    }

    public function script(DeploymentScript $script): JsonResponse
    {
        return response()->json($script);
    }

    public function testRedis(TestRedisRequest $request): JsonResponse
    {
        $profile = RedisProfile::query()->findOrFail($request->integer('redis_profile_id'));
        $result = $this->redisConnectionService->testConnection($profile, Auth::user(), $request->integer('ttl'));

        return response()->json($result);
    }

    public function testSmtp(TestSmtpRequest $request): JsonResponse
    {
        $profile = SmtpProfile::query()->findOrFail($request->integer('smtp_profile_id'));
        $this->smtpService->sendTestEmail($profile, $request->string('recipient_email')->toString());

        return response()->json(['message' => 'SMTP test sent']);
    }

    public function testTelegram(TestTelegramRequest $request): JsonResponse
    {
        $connection = TelegramConnection::query()->findOrFail($request->integer('telegram_connection_id'));

        return response()->json($this->telegramService->test($connection));
    }
}
