<?php

namespace App\Contracts\Services;

use App\DTOs\DeploymentResultData;
use App\Models\DeploymentScript;
use App\Models\User;

interface DeploymentServiceInterface
{
    public function queue(DeploymentScript $script, ?User $user, string $triggeredVia = 'manual'): void;

    public function execute(DeploymentScript $script, ?User $user, string $triggeredVia = 'manual'): DeploymentResultData;
}
