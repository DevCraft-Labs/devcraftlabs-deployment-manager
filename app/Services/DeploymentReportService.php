<?php

namespace App\Services;

use App\Contracts\Services\DeploymentReportServiceInterface;
use App\Exports\DeploymentExecutionsExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DeploymentReportService implements DeploymentReportServiceInterface
{
    public function download(array $filters = []): BinaryFileResponse
    {
        $file = 'deployment-report-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new DeploymentExecutionsExport($filters), $file);
    }
}
