<?php

namespace App\Http\Requests\Ged;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGedCategorieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('categorie')) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $id = $this->route('categorie')?->id;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('ged_categories', 'code')->ignore($id)],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'integer', 'exists:ged_categories,id', Rule::notIn([$id])],
            'ordre' => ['nullable', 'integer', 'min:0'],
            'couleur' => ['nullable', 'string', 'max:20'],
            'icone' => ['nullable', 'string', 'max:100'],
            'statut' => ['required', 'in:actif,inactif'],
        ];
    }
}
