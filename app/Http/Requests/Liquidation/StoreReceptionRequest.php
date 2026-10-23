<?php

namespace App\Http\Requests\Liquidation;

use App\Enums\TypeReceptionLiquidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReceptionRequest extends FormRequest
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
            'type_reception' => ['required', Rule::in(array_map(fn ($t) => $t->value, TypeReceptionLiquidation::cases()))],
            'date_reception' => ['required', 'date'],
            'numero_pv' => ['nullable', 'string', 'max:100'],
            'commission' => ['nullable', 'string', 'max:2000'],
            'responsable_id' => ['nullable', 'integer', 'exists:users,id'],
            'conforme' => ['nullable', 'boolean'],
            'reserves' => ['nullable', 'string', 'max:2000'],
            'observations' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('conforme')) {
            $this->merge(['conforme' => $this->boolean('conforme')]);
        }
    }
}
