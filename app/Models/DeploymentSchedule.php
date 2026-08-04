<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeploymentSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\DeploymentScheduleFactory> */
    use HasFactory;

    protected $fillable = [
        'deployment_script_id',
        'cron_expression',
        'enabled',
        'last_run_at',
        'next_run_at',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'last_run_at' => 'datetime',
            'next_run_at' => 'datetime',
        ];
    }

    public function script(): BelongsTo
    {
        return $this->belongsTo(DeploymentScript::class, 'deployment_script_id');
    }
}
