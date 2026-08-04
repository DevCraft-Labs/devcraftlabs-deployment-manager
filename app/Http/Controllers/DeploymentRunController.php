<?php

namespace App\Http\Controllers;

use App\Models\DeploymentExecution;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeploymentRunController extends Controller
{
    public function logs(DeploymentExecution $execution): StreamedResponse
    {
        $content = "STDOUT\n" . ($execution->stdout ?? '') . "\n\nSTDERR\n" . ($execution->stderr ?? '');

        return response()->streamDownload(static function () use ($content): void {
            echo $content;
        }, 'deployment-log-' . $execution->id . '.log');
    }
}
