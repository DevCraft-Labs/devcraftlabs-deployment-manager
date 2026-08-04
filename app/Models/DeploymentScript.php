<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeploymentScript extends Model
{
    /** @use HasFactory<\Database\Factories\DeploymentScriptFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'working_directory',
        'script_content',
        'timeout',
        'telegram_connection_id',
        'smtp_profile_id',
        'redis_profile_id',
        'cron_enabled',
        'cron_expression',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'cron_enabled' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function redisProfile(): BelongsTo
    {
        return $this->belongsTo(RedisProfile::class);
    }

    public function smtpProfile(): BelongsTo
    {
        return $this->belongsTo(SmtpProfile::class);
    }

    public function telegramConnection(): BelongsTo
    {
        return $this->belongsTo(TelegramConnection::class);
    }

    public function executions(): HasMany
    {
        return $this->hasMany(DeploymentExecution::class);
    }

    public function schedule(): HasMany
    {
        return $this->hasMany(DeploymentSchedule::class);
    }
}
