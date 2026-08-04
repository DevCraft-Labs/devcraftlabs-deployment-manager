<?php

namespace App\Support;

use App\Models\ActivityAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public function log(string $action, ?string $entityType = null, ?int $entityId = null, array $context = []): void
    {
        $request = request();

        ActivityAudit::query()->create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip_address' => $request instanceof Request ? $request->ip() : null,
            'context' => $context,
        ]);
    }
}
