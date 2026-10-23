<?php

namespace App\Http\Requests\Liquidation;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceFaitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('liquidation.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date_constatation' => ['required', 'date'],
            'responsable_id' => ['nullable', 'integer', 'exists:users,id'],
            'commission' => ['nullable', 'string', 'max:255'],
            'conformite_quantitative' => ['nullable', 'boolean'],
            'conformite_qualitative' => ['nullable', 'boolean'],
            'conformite_technique' => ['nullable', 'boolean'],
            'conformite_contractuelle' => ['nullable', 'boolean'],
            'reserves' => ['nullable', 'string', 'max:2000'],
            'observations' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'conformite_quantitative' => $this->boolean('conformite_quantitative'),
            'conformite_qualitative' => $this->boolean('conformite_qualitative'),
            'conformite_technique' => $this->boolean('conformite_technique'),
            'conformite_contractuelle' => $this->boolean('conformite_contractuelle'),
        ]);
    }
}
