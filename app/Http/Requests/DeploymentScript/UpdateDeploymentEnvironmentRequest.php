<?php

namespace App\Http\Requests\DeploymentScript;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeploymentEnvironmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('scripts.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'contents' => ['nullable', 'string', 'max:262144'],
        ];
    }
}