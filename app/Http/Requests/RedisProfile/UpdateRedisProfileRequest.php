<?php

namespace App\Http\Requests\RedisProfile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRedisProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('redis_profile')?->id ?? $this->route('redis_profile');

        return [
            'name' => ['required', 'string', 'max:255', 'unique:redis_profiles,name,' . $id],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'database' => ['required', 'integer', 'min:0'],
            'tls' => ['nullable', 'boolean'],
            'timeout' => ['required', 'integer', 'min:1', 'max:60'],
            'default_ttl' => ['required', 'integer', 'min:1', 'max:86400'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
