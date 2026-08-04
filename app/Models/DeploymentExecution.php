<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeploymentExecution extends Model
{
    /** @use HasFactory<\Database\Factories\DeploymentExecutionFactory> */
    use HasFactory;

    protected $fillable = [
        'deployment_script_id',
        'triggered_by_user_id',
        'triggered_via',
        'started_at',
        'finished_at',
        'duration_ms',
        'exit_code',
        'is_success',
        'stdout',
        'stderr',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'is_success' => 'boolean',
        ];
    }

    public function script(): BelongsTo
    {
        return $this->belongsTo(DeploymentScript::class, 'deployment_script_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }
}
