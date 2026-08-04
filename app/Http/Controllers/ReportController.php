<?php

namespace App\Http\Controllers;

use App\Contracts\Services\DeploymentReportServiceInterface;
use App\Jobs\GenerateDeploymentReportJob;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(private readonly DeploymentReportServiceInterface $reportService)
    {
    }

    public function download(Request $request): BinaryFileResponse
    {
        return $this->reportService->download($request->only('status'));
    }

    public function queue(Request $request)
    {
        GenerateDeploymentReportJob::dispatch($request->only('status'));

        return back()->with('status', 'Report generation queued.');
    }
}
