<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmtpProfile extends Model
{
    /** @use HasFactory<\Database\Factories\SmtpProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_email',
        'from_name',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'status' => 'boolean',
        ];
    }

    public function scripts(): HasMany
    {
        return $this->hasMany(DeploymentScript::class);
    }
}
