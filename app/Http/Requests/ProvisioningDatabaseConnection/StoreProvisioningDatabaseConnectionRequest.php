<?php

namespace App\Http\Requests\ProvisioningDatabaseConnection;

use Illuminate\Foundation\Http\FormRequest;

class StoreProvisioningDatabaseConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provisioning.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:provisioning_database_connections,name'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
