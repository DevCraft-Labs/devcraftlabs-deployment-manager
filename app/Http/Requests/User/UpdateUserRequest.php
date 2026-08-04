<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.manage') ?? false;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')->ignore($user)],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:12', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['Owner', 'Developer', 'Viewer'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
