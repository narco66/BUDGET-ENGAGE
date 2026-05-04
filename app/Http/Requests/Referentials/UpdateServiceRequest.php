<?php

namespace App\Http\Requests\Referentials;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceRequest extends FormRequest
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
            'direction_id' => ['required', 'integer', 'exists:directions,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('services', 'code')
                    ->where(fn ($query) => $query->where('direction_id', $this->integer('direction_id')))
                    ->ignore($this->route('service')->id),
            ],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'statut' => ['required', 'in:actif,inactif'],
        ];
    }
}
