<?php

namespace App\Http\Requests\Ordonnancement;

use App\Enums\ModeCreationOrdonnancement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrdonnancementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ordonnancement.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'liquidation_id' => ['required', 'integer', 'exists:liquidations,id'],
            'ordonnancement_type_id' => ['nullable', 'integer', 'exists:ordonnancement_types,id'],
            'mode_creation' => ['nullable', Rule::in(array_map(fn ($m) => $m->value, ModeCreationOrdonnancement::cases()))],
            'fournisseur_id' => ['nullable', 'integer', 'exists:fournisseurs,id'],
            'objet' => ['nullable', 'string', 'max:1000'],
            'devise' => ['nullable', 'string', 'max:10'],
            'date_ordonnancement' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'justification' => ['nullable', 'string', 'max:2000'],
            'commentaire' => ['nullable', 'string'],

            'lignes' => ['required', 'array', 'min:1'],
            'lignes.*.designation' => ['required', 'string', 'max:255'],
            'lignes.*.ligne_budgetaire_id' => ['required', 'integer', 'exists:lignes_budgetaires,id'],
            'lignes.*.liquidation_ligne_id' => ['nullable', 'integer', 'exists:liquidation_lignes,id'],
            'lignes.*.engagement_imputation_id' => ['nullable', 'integer', 'exists:engagement_imputations,id'],
            'lignes.*.source_financement_id' => ['nullable', 'integer', 'exists:sources_financement,id'],
            'lignes.*.activite_id' => ['nullable', 'integer', 'exists:activites,id'],
            'lignes.*.centre_cout' => ['nullable', 'string', 'max:100'],
            'lignes.*.centre_responsabilite' => ['nullable', 'string', 'max:100'],
            'lignes.*.montant_liquide' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.montant_ordonnance' => ['required', 'numeric', 'min:0'],
            'lignes.*.mode_paiement' => ['nullable', 'string', 'max:60'],
            'lignes.*.echeance' => ['nullable', 'date'],
            'lignes.*.banque' => ['nullable', 'string', 'max:150'],
            'lignes.*.compte_bancaire' => ['nullable', 'string', 'max:60'],
            'lignes.*.observation' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $mode = ModeCreationOrdonnancement::tryFrom((string) $this->input('mode_creation', ModeCreationOrdonnancement::Manuel->value));
            if ($mode?->exigeJustification() && trim((string) $this->input('justification')) === '') {
                $v->errors()->add('justification', __('Une justification est obligatoire pour ce mode de création.'));
            }
        });
    }
}
