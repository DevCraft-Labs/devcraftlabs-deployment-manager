<?php

namespace App\Contracts\Services;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface DeploymentReportServiceInterface
{
    public function download(array $filters = []): BinaryFileResponse;
}
