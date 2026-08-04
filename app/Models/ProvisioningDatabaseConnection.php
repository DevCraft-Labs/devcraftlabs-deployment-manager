<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProvisioningDatabaseConnection extends Model
{
    /** @use HasFactory<\Database\Factories\ProvisioningDatabaseConnectionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'host',
        'port',
        'username',
        'password',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'status' => 'boolean',
        ];
    }
}
