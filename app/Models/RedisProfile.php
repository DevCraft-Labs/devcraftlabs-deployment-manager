<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RedisProfile extends Model
{
    /** @use HasFactory<\Database\Factories\RedisProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'host',
        'port',
        'username',
        'password',
        'database',
        'tls',
        'timeout',
        'default_ttl',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'tls' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function scripts(): HasMany
    {
        return $this->hasMany(DeploymentScript::class);
    }
}
