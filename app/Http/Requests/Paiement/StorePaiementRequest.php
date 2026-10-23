<?php

namespace App\Http\Requests\Paiement;

use App\Enums\ModeCreationPaiement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePaiementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('paiement.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ordonnancement_id' => ['required', 'integer', 'exists:ordonnancements,id'],
            'paiement_type_id' => ['nullable', 'integer', 'exists:paiement_types,id'],
            'paiement_banque_id' => ['nullable', 'integer', 'exists:paiement_banques,id'],
            'mode_creation' => ['nullable', Rule::in(array_map(fn ($m) => $m->value, ModeCreationPaiement::cases()))],
            'mode_paiement' => ['nullable', 'string', 'max:60'],
            'objet' => ['nullable', 'string', 'max:1000'],
            'devise' => ['nullable', 'string', 'max:10'],
            'devise_paiement' => ['nullable', 'string', 'max:10'],
            'taux_change' => ['nullable', 'numeric', 'min:0'],
            'frais_bancaires' => ['nullable', 'numeric', 'min:0'],
            'priorite' => ['nullable', 'string', 'max:20'],
            'date_prevue' => ['nullable', 'date'],
            'date_paiement' => ['nullable', 'date'],
            'reference_paiement' => ['nullable', 'string', 'max:100'],
            'justification' => ['nullable', 'string', 'max:2000'],
            'commentaire' => ['nullable', 'string'],

            'lignes' => ['required', 'array', 'min:1'],
            'lignes.*.designation' => ['required', 'string', 'max:255'],
            'lignes.*.ligne_budgetaire_id' => ['required', 'integer', 'exists:lignes_budgetaires,id'],
            'lignes.*.ordonnancement_ligne_id' => ['nullable', 'integer', 'exists:ordonnancement_lignes,id'],
            'lignes.*.engagement_imputation_id' => ['nullable', 'integer', 'exists:engagement_imputations,id'],
            'lignes.*.source_financement_id' => ['nullable', 'integer', 'exists:sources_financement,id'],
            'lignes.*.activite_id' => ['nullable', 'integer', 'exists:activites,id'],
            'lignes.*.montant_ordonnance' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.montant_paye' => ['required', 'numeric', 'min:0'],
            'lignes.*.frais_bancaires' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.reference_bancaire' => ['nullable', 'string', 'max:100'],
            'lignes.*.observation' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $mode = ModeCreationPaiement::tryFrom((string) $this->input('mode_creation', ModeCreationPaiement::Manuel->value));
            if ($mode?->exigeJustification() && trim((string) $this->input('justification')) === '') {
                $v->errors()->add('justification', __('Une justification est obligatoire pour ce mode de création.'));
            }
        });
    }
}
