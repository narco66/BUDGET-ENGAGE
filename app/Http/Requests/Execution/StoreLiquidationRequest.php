<?php

namespace App\Http\Requests\Execution;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreLiquidationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('createLiquidation', $this->route('engagement')) ?? false;
    }

    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    public function rules(): array
    {
        return [
            'engagement_imputation_id' => ['nullable', 'integer', 'exists:engagement_imputations,id'],
            'montant_liquide' => ['required', 'numeric', 'min:0.01'],
            'date_liquidation' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'commentaire' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $engagement = $this->route('engagement');
            $imputationId = $this->integer('engagement_imputation_id');
            if ($engagement === null) {
                return;
            }

            if ($engagement->imputations()->exists() && $imputationId <= 0) {
                $v->errors()->add('engagement_imputation_id', __('Veuillez sélectionner une imputation.'));

                return;
            }

            if ($imputationId <= 0) {
                return;
            }

            $belongs = $engagement->imputations()->whereKey($imputationId)->exists();
            if (! $belongs) {
                $v->errors()->add('engagement_imputation_id', __('Imputation invalide pour cet engagement.'));
            }
        });
    }
}
