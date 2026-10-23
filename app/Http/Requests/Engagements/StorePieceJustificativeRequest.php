<?php

namespace App\Http\Requests\Engagements;

use Illuminate\Foundation\Http\FormRequest;

class StorePieceJustificativeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('engagement')) ?? false;
    }

    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    public function rules(): array
    {
        return [
            'piece' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx'],
            'type_piece' => ['nullable', 'string', 'max:100'],
        ];
    }
}
