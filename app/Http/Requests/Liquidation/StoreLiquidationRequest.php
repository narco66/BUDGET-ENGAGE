<?php

namespace App\Http\Requests\Liquidation;

use App\Enums\ModeCreationLiquidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreLiquidationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('liquidation.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'engagement_id' => ['required', 'integer', 'exists:engagements,id'],
            'liquidation_type_id' => ['nullable', 'integer', 'exists:liquidation_types,id'],
            'mode_creation' => ['nullable', Rule::in(array_map(fn ($m) => $m->value, ModeCreationLiquidation::cases()))],
            'fournisseur_id' => ['nullable', 'integer', 'exists:fournisseurs,id'],
            'objet' => ['nullable', 'string', 'max:1000'],
            'devise' => ['nullable', 'string', 'max:10'],
            'date_liquidation' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'justification' => ['nullable', 'string', 'max:2000'],
            'commentaire' => ['nullable', 'string'],

            'lignes' => ['required', 'array', 'min:1'],
            'lignes.*.designation' => ['required', 'string', 'max:255'],
            'lignes.*.ligne_budgetaire_id' => ['required', 'integer', 'exists:lignes_budgetaires,id'],
            'lignes.*.engagement_imputation_id' => ['nullable', 'integer', 'exists:engagement_imputations,id'],
            'lignes.*.source_financement_id' => ['nullable', 'integer', 'exists:sources_financement,id'],
            'lignes.*.activite_id' => ['nullable', 'integer', 'exists:activites,id'],
            'lignes.*.tache_id' => ['nullable', 'integer', 'exists:taches,id'],
            'lignes.*.centre_cout' => ['nullable', 'string', 'max:100'],
            'lignes.*.centre_responsabilite' => ['nullable', 'string', 'max:100'],
            'lignes.*.quantite_prevue' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.quantite_realisee' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.prix_unitaire' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.montant_brut' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.montant_avances' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.montant_acomptes' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.montant_retenues' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.montant_garanties' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.montant_penalites' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.montant_taxes' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.montant_revision_prix' => ['nullable', 'numeric'],
            'lignes.*.observation' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $mode = ModeCreationLiquidation::tryFrom((string) $this->input('mode_creation', ModeCreationLiquidation::Manuel->value));
            if ($mode?->exigeJustification() && trim((string) $this->input('justification')) === '') {
                $v->errors()->add('justification', __('Une justification est obligatoire pour ce mode de création.'));
            }
        });
    }
}
