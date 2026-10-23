<?php

namespace App\Http\Requests\Execution;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrdonnancementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('ordonnancement')) ?? false;
    }

    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    public function rules(): array
    {
        return [
            'montant_ordonnance' => ['required', 'numeric', 'min:0.01'],
            'date_ordonnancement' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'commentaire' => ['nullable', 'string'],
        ];
    }
}
