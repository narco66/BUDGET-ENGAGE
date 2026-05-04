<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreParametreControleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('parameters.manage') ?? false;
    }

    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    public function rules(): array
    {
        return [
            'cle' => ['required', 'string', 'max:100', 'unique:parametres_controle,cle'],
            'valeur' => ['required', 'string'],
            'type_valeur' => ['required', Rule::in(['string', 'integer', 'decimal', 'boolean', 'json', 'date'])],
            'description' => ['nullable', 'string'],
            'actif' => ['sometimes', 'boolean'],
        ];
    }
}
