<?php

namespace App\Http\Requests\Ged;

use App\Models\GedCategorie;
use Illuminate\Foundation\Http\FormRequest;

class StoreGedCategorieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', GedCategorie::class) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:ged_categories,code'],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:ged_categories,id'],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'couleur' => ['nullable', 'string', 'max:20'],
            'icone' => ['nullable', 'string', 'max:100'],
            'statut' => ['required', 'in:actif,inactif'],
        ];
    }
}
