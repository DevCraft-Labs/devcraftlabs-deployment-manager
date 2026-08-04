<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConnectionTestHistory extends Model
{
    /** @use HasFactory<\Database\Factories\ConnectionTestHistoryFactory> */
    use HasFactory;

    protected $fillable = [
        'connection_type',
        'connection_id',
        'is_success',
        'latency_ms',
        'ttl',
        'response',
        'meta',
        'tested_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'is_success' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function testedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tested_by_user_id');
    }
}
