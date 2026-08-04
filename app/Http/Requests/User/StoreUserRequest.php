<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['Owner', 'Developer', 'Viewer'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
