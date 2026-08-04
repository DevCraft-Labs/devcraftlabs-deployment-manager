<?php

namespace App\Jobs;

use App\Contracts\Services\DeploymentReportServiceInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateDeploymentReportJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly array $filters = [])
    {
    }

    /**
     * Execute the job.
     */
    public function handle(DeploymentReportServiceInterface $reportService): void
    {
        $reportService->download($this->filters);
    }
}
