<?php

namespace App\Providers;

use App\Contracts\Repositories\DeploymentExecutionRepositoryInterface;
use App\Contracts\Repositories\DeploymentScriptRepositoryInterface;
use App\Contracts\Repositories\RedisProfileRepositoryInterface;
use App\Contracts\Services\DeploymentReportServiceInterface;
use App\Contracts\Services\DeploymentServiceInterface;
use App\Contracts\Services\ProvisioningDatabaseServiceInterface;
use App\Contracts\Services\RedisConnectionServiceInterface;
use App\Contracts\Services\SmtpServiceInterface;
use App\Contracts\Services\TelegramServiceInterface;
use App\Contracts\Services\TemporaryClipboardServiceInterface;
use App\Contracts\Services\TemporaryFileClipboardServiceInterface;
use App\Models\DeploymentScript;
use App\Policies\DeploymentScriptPolicy;
use App\Repositories\DeploymentExecutionRepository;
use App\Repositories\DeploymentScriptRepository;
use App\Repositories\RedisProfileRepository;
use App\Services\DeploymentReportService;
use App\Services\DeploymentService;
use App\Services\ProvisioningDatabaseService;
use App\Services\RedisConnectionService;
use App\Services\SmtpService;
use App\Services\TelegramService;
use App\Services\TemporaryClipboardService;
use App\Services\TemporaryFileClipboardService;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(DeploymentScriptRepositoryInterface::class, DeploymentScriptRepository::class);
        $this->app->bind(DeploymentExecutionRepositoryInterface::class, DeploymentExecutionRepository::class);
        $this->app->bind(RedisProfileRepositoryInterface::class, RedisProfileRepository::class);

        $this->app->bind(DeploymentServiceInterface::class, DeploymentService::class);
        $this->app->bind(RedisConnectionServiceInterface::class, RedisConnectionService::class);
        $this->app->bind(SmtpServiceInterface::class, SmtpService::class);
        $this->app->bind(TelegramServiceInterface::class, TelegramService::class);
        $this->app->bind(TemporaryClipboardServiceInterface::class, TemporaryClipboardService::class);
        $this->app->bind(TemporaryFileClipboardServiceInterface::class, TemporaryFileClipboardService::class);
        $this->app->bind(ProvisioningDatabaseServiceInterface::class, ProvisioningDatabaseService::class);
        $this->app->bind(DeploymentReportServiceInterface::class, DeploymentReportService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Gate::before(static function (User $user): ?bool {
            return $user->hasRole('Owner') ? true : null;
        });

        Gate::policy(DeploymentScript::class, DeploymentScriptPolicy::class);
    }
}
