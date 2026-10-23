<?php

namespace App\Http\Requests\Ordonnancement;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrdonnancementDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ordonnancement.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fichier' => ['required', 'file', 'max:51200', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,zip'],
            'titre' => ['nullable', 'string', 'max:255'],
            'type_piece' => ['nullable', 'string', 'max:100'],
        ];
    }
}
