<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelegramConnection extends Model
{
    /** @use HasFactory<\Database\Factories\TelegramConnectionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'bot_token',
        'chat_id',
        'allowed_chat_ids',
        'webhook_secret',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'bot_token' => 'encrypted',
            'webhook_secret' => 'encrypted',
            'status' => 'boolean',
        ];
    }

    public function scripts(): HasMany
    {
        return $this->hasMany(DeploymentScript::class);
    }
}
