<?php

namespace App\Http\Requests\FileClipboard;

use Illuminate\Foundation\Http\FormRequest;

class StoreFileClipboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:' . (int) config('clipboard.max_file_size_kb', 10240)],
        ];
    }
}
