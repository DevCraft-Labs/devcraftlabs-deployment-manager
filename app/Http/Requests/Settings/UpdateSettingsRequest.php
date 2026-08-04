<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
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
        return [
            'server_name' => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'timezone'],
            'default_telegram_connection_id' => ['nullable', 'integer', 'exists:telegram_connections,id'],
            'default_smtp_profile_id' => ['nullable', 'integer', 'exists:smtp_profiles,id'],
            'default_redis_profile_id' => ['nullable', 'integer', 'exists:redis_profiles,id'],
            'retention_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'log_cleanup' => ['nullable', 'boolean'],
        ];
    }
}
