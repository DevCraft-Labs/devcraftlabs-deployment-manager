<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationSetting extends Model
{
    /** @use HasFactory<\Database\Factories\ApplicationSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'server_name',
        'timezone',
        'default_telegram_connection_id',
        'default_smtp_profile_id',
        'default_redis_profile_id',
        'retention_days',
        'log_cleanup',
    ];

    protected function casts(): array
    {
        return [
            'log_cleanup' => 'boolean',
        ];
    }

    public function defaultTelegram(): BelongsTo
    {
        return $this->belongsTo(TelegramConnection::class, 'default_telegram_connection_id');
    }

    public function defaultSmtp(): BelongsTo
    {
        return $this->belongsTo(SmtpProfile::class, 'default_smtp_profile_id');
    }

    public function defaultRedis(): BelongsTo
    {
        return $this->belongsTo(RedisProfile::class, 'default_redis_profile_id');
    }
}
