<?php

namespace App\Http\Requests\TelegramConnection;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTelegramConnectionRequest extends FormRequest
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
        $id = $this->route('telegram_connection')?->id ?? $this->route('telegram_connection');

        return [
            'name' => ['required', 'string', 'max:255', 'unique:telegram_connections,name,' . $id],
            'bot_token' => ['required', 'string', 'max:255'],
            'chat_id' => ['required', 'string', 'max:255'],
            'allowed_chat_ids' => ['nullable', 'string', 'max:1000'],
            'webhook_secret' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
        ];
    }
}
