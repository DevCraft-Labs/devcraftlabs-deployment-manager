<?php

namespace App\Http\Requests\ProvisioningDatabaseConnection;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProvisioningDatabaseConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provisioning.update') ?? false;
    }

    public function rules(): array
    {
        $connection = $this->route('connection');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('provisioning_database_connections', 'name')->ignore($connection)],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'between:1,65535'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
