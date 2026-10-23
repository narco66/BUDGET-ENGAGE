<?php

namespace App\Http\Requests\ExpressionBesoins;

use App\Enums\CategorieBesoinHorsPap;
use App\Enums\NiveauUrgenceBesoin;
use App\Enums\PrioriteBesoin;
use App\Enums\StatutExerciceBudgetaire;
use App\Enums\TypeBesoin;
use App\Models\ExerciceBudgetaire;
use App\Models\ExpressionBesoin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreExpressionBesoinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ExpressionBesoin::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        // Élimine les lignes totalement vides (sans désignation) avant validation.
        $lignes = collect($this->input('lignes', []))
            ->filter(fn ($l) => filled($l['designation'] ?? null))
            ->values()
            ->all();

        $this->merge(['lignes' => $lignes]);
    }

    private function estPap(): bool
    {
        return $this->input('type_besoin') === TypeBesoin::Pap->value;
    }

    private function estHorsPap(): bool
    {
        return $this->input('type_besoin') === TypeBesoin::HorsPap->value;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'exercice_budgetaire_id' => ['required', 'integer', 'exists:exercices_budgetaires,id'],
            'type_besoin' => ['required', Rule::enum(TypeBesoin::class)],
            'structure_demandeuse' => ['nullable', 'string', 'max:255'],
            'departement_id' => ['nullable', 'integer', 'exists:departements,id'],
            'direction_id' => ['nullable', 'integer', 'exists:directions,id'],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'demandeur_id' => ['nullable', 'integer', 'exists:users,id'],
            'date_besoin' => ['nullable', 'date'],
            'objet' => ['required', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'priorite' => ['nullable', Rule::enum(PrioriteBesoin::class)],
            'niveau_urgence' => ['nullable', Rule::enum(NiveauUrgenceBesoin::class)],

            // Onglet 2 — justification (obligatoire pour les besoins Hors PAP).
            'justification' => [Rule::requiredIf(fn () => $this->estHorsPap()), 'nullable', 'string'],
            'objectif_recherche' => ['nullable', 'string'],
            'consequences_non_realisation' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],

            // Onglet 3 — rattachement PAP (imposé si type = PAP).
            'pilier_id' => [Rule::requiredIf(fn () => $this->estPap()), 'nullable', 'integer', 'exists:piliers,id'],
            'axe_strategique_id' => [Rule::requiredIf(fn () => $this->estPap()), 'nullable', 'integer', 'exists:axes_strategiques,id'],
            'produit_id' => [Rule::requiredIf(fn () => $this->estPap()), 'nullable', 'integer', 'exists:produits,id'],
            'sous_produit_id' => [Rule::requiredIf(fn () => $this->estPap()), 'nullable', 'integer', 'exists:sous_produits,id'],
            'activite_id' => [Rule::requiredIf(fn () => $this->estPap()), 'nullable', 'integer', 'exists:activites,id'],
            'tache_id' => ['nullable', 'integer', 'exists:taches,id'],
            'indicateur_id' => ['nullable', 'integer', 'exists:indicateurs,id'],
            'responsable_activite' => ['nullable', 'string', 'max:255'],

            // Onglet 3 — rattachement Hors PAP (catégorie imposée).
            'categorie_hors_pap' => [Rule::requiredIf(fn () => $this->estHorsPap()), 'nullable', Rule::enum(CategorieBesoinHorsPap::class)],
            'sous_categorie' => ['nullable', 'string', 'max:255'],
            'nature_besoin' => ['nullable', 'string', 'max:255'],
            'centre_cout' => ['nullable', 'string', 'max:255'],
            'centre_responsabilite' => ['nullable', 'string', 'max:255'],

            // Onglet 4 — lignes (au moins une, contrôlée indépendamment).
            'lignes' => ['required', 'array', 'min:1'],
            'lignes.*.designation' => ['required', 'string', 'max:255'],
            'lignes.*.code_article' => ['nullable', 'string', 'max:60'],
            'lignes.*.categorie' => ['nullable', 'string', 'max:40'],
            'lignes.*.specifications' => ['nullable', 'string', 'max:1000'],
            'lignes.*.quantite' => ['required', 'numeric', 'min:0'],
            'lignes.*.unite' => ['nullable', 'string', 'max:50'],
            'lignes.*.prix_unitaire' => ['required', 'numeric', 'min:0'],
            'lignes.*.priorite' => ['nullable', 'string', 'max:20'],
            'lignes.*.echeance' => ['nullable', 'date'],
            'lignes.*.ligne_budgetaire_id' => [
                'required', 'integer',
                Rule::exists('lignes_budgetaires', 'id')->where(
                    fn ($q) => $q->where('exercice_budgetaire_id', $this->integer('exercice_budgetaire_id'))
                ),
            ],
            'lignes.*.source_financement_id' => ['required', 'integer', 'exists:sources_financement,id'],
            'lignes.*.activite_id' => ['nullable', 'integer', 'exists:activites,id'],
            'lignes.*.observation' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            // Interdit la saisie sur un exercice clôturé ou archivé.
            $exercice = ExerciceBudgetaire::query()->find($this->input('exercice_budgetaire_id'));
            if ($exercice !== null) {
                $statut = $exercice->statut instanceof StatutExerciceBudgetaire
                    ? $exercice->statut->value
                    : (string) $exercice->statut;

                if (in_array($statut, ['cloture', 'archive'], true)) {
                    $v->errors()->add('exercice_budgetaire_id', __('Impossible de saisir un besoin sur un exercice clôturé ou archivé.'));
                }
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lignes.required' => __('Le besoin doit comporter au moins une ligne.'),
            'lignes.min' => __('Le besoin doit comporter au moins une ligne.'),
            'lignes.*.ligne_budgetaire_id.required' => __('Chaque ligne doit être imputée à une ligne budgétaire.'),
            'lignes.*.source_financement_id.required' => __('Chaque ligne doit avoir une source de financement.'),
            'activite_id.required' => __('Un besoin PAP doit être rattaché à une activité du PAP.'),
            'categorie_hors_pap.required' => __('Un besoin Hors PAP doit avoir une catégorie.'),
            'justification.required' => __('La justification est obligatoire pour un besoin Hors PAP.'),
        ];
    }
}
