<?php

namespace App\Http\Requests\Engagements;

use App\Enums\BeneficiaireType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEngagementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('engagement')) ?? false;
    }

    /**
     * @return array<string, array<int, mixed|string>|string>
     */
    public function rules(): array
    {
        return [
            'exercice_budgetaire_id' => ['required', 'integer', 'exists:exercices_budgetaires,id'],
            'ligne_budgetaire_id' => [
                'required',
                'integer',
                'exists:lignes_budgetaires,id',
                Rule::exists('lignes_budgetaires', 'id')->where(
                    fn ($query) => $query->where('exercice_budgetaire_id', $this->integer('exercice_budgetaire_id'))
                ),
            ],
            'departement_id' => ['nullable', 'integer', 'exists:departements,id'],
            'direction_id' => ['nullable', 'integer', 'exists:directions,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'type_engagement' => ['required', 'string', 'max:100'],
            'beneficiaire_type' => ['required', Rule::enum(BeneficiaireType::class)],
            'beneficiaire_id' => ['nullable', 'integer'],
            'beneficiaire_autre' => ['nullable', 'string'],
            'objet' => ['required', 'string'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'date_engagement' => ['nullable', 'date'],
            'is_urgence' => ['sometimes', 'boolean'],
            'justification_urgence' => ['nullable', 'string', 'required_if:is_urgence,1,true'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $type = $this->input('beneficiaire_type');
            if (in_array($type, [BeneficiaireType::Fournisseur->value, BeneficiaireType::Agent->value], true)) {
                if (! $this->filled('beneficiaire_id')) {
                    $v->errors()->add('beneficiaire_id', __('Le bénéficiaire est obligatoire pour ce type.'));

                    return;
                }
                $table = $type === BeneficiaireType::Fournisseur->value ? 'fournisseurs' : 'agents';
                if (! \Illuminate\Support\Facades\DB::table($table)->where('id', $this->integer('beneficiaire_id'))->exists()) {
                    $v->errors()->add('beneficiaire_id', __('Bénéficiaire invalide.'));
                }
            }
            if ($type === BeneficiaireType::Autre->value && ! $this->filled('beneficiaire_autre')) {
                $v->errors()->add('beneficiaire_autre', __('Précisez le bénéficiaire.'));
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_urgence' => $this->boolean('is_urgence'),
        ]);
    }
}
