<?php

namespace App\Http\Requests\Clipboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClipboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['content' => ['required', 'string', 'max:100000']];
    }
}