<?php

namespace App\Http\Requests\DeploymentScript;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDeploymentScriptRequest extends FormRequest
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
        $id = $this->route('deployment_script')?->id ?? $this->route('deployment_script');

        return [
            'name' => ['required', 'string', 'max:255', 'unique:deployment_scripts,name,' . $id],
            'description' => ['nullable', 'string'],
            'working_directory' => ['required', 'string', 'max:500'],
            'script_content' => ['required', 'string'],
            'timeout' => ['required', 'integer', 'min:10', 'max:3600'],
            'telegram_connection_id' => ['nullable', 'integer', 'exists:telegram_connections,id'],
            'smtp_profile_id' => ['nullable', 'integer', 'exists:smtp_profiles,id'],
            'redis_profile_id' => ['nullable', 'integer', 'exists:redis_profiles,id'],
            'cron_enabled' => ['nullable', 'boolean'],
            'cron_expression' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
