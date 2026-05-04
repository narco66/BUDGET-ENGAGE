<?php

namespace App\Http\Requests\Referentials;

use Illuminate\Foundation\Http\FormRequest;

class StoreNatureDepenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('referentials.manage') ?? false;
    }

    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:natures_depenses,code'],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'statut' => ['required', 'in:actif,inactif'],
        ];
    }
}
