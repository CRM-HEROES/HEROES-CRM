<?php

namespace App\Http\Requests\Project\Document;

use Illuminate\Foundation\Http\FormRequest;

class FontRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // 'file' => 'required|file|mimes:ttf'
        ];
    }
}
