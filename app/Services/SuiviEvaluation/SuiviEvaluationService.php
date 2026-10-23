<?php

namespace App\Services\SuiviEvaluation;

use App\Enums\StatutSuiviRealisation;
use App\Models\ExerciceBudgetaire;
use App\Models\Indicateur;
use App\Models\IndicateurRealisation;
use App\Models\User;
use Illuminate\Support\Collection;

class SuiviEvaluationService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function enregistrer(array $data, User $user): IndicateurRealisation
    {
        return IndicateurRealisation::query()->create([
            ...$data,
            'statut' => StatutSuiviRealisation::Brouillon,
            'created_by' => $user->id,
        ]);
    }

    public function valider(IndicateurRealisation $realisation, User $user): IndicateurRealisation
    {
        abort_if(! in_array($realisation->statut, [StatutSuiviRealisation::Brouillon, StatutSuiviRealisation::Soumis], true), 422);

        $realisation->update([
            'statut' => StatutSuiviRealisation::Valide,
            'validated_by' => $user->id,
            'validated_at' => now(),
        ]);

        return $realisation->fresh();
    }

    /**
     * @return array<string, float|int>
     */
    public function syntheseTrimestre(ExerciceBudgetaire $exercice, int $trimestre): array
    {
        $realisations = IndicateurRealisation::query()
            ->where('exercice_budgetaire_id', $exercice->id)
            ->where('trimestre', $trimestre)
            ->where('annee', $exercice->annee)
            ->where('statut', StatutSuiviRealisation::Valide)
            ->get();

        $avecCible = $realisations->filter(fn (IndicateurRealisation $r) => $r->valeur_cible !== null && (float) $r->valeur_cible > 0);
        $tauxMoyen = $avecCible->isEmpty()
            ? 0.0
            : round($avecCible->avg(fn (IndicateurRealisation $r) => $r->tauxRealisation() ?? 0), 1);

        return [
            'trimestre' => $trimestre,
            'annee' => $exercice->annee,
            'nb_indicateurs_saisis' => $realisations->count(),
            'taux_realisation_moyen' => $tauxMoyen,
        ];
    }

    /**
     * Rapport trimestriel institutionnel.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function rapportTrimestriel(ExerciceBudgetaire $exercice, int $trimestre): Collection
    {
        $indicateurs = Indicateur::query()
            ->with(['typeIndicateur', 'uniteMesure'])
            ->where('statut', 'actif')
            ->orderBy('code')
            ->get();

        $realisations = IndicateurRealisation::query()
            ->where('exercice_budgetaire_id', $exercice->id)
            ->where('trimestre', $trimestre)
            ->where('annee', $exercice->annee)
            ->where('statut', StatutSuiviRealisation::Valide)
            ->get()
            ->keyBy('indicateur_id');

        return $indicateurs->map(function (Indicateur $indicateur) use ($realisations): array {
            $realisation = $realisations->get($indicateur->id);

            return [
                'indicateur_id' => $indicateur->id,
                'code' => $indicateur->code,
                'libelle' => $indicateur->libelle,
                'type' => $indicateur->typeIndicateur?->libelle,
                'unite' => $indicateur->uniteMesure?->libelle,
                'valeur_cible' => $realisation ? (float) $realisation->valeur_cible : null,
                'valeur_realisee' => $realisation ? (float) $realisation->valeur_realisee : null,
                'taux_realisation' => $realisation?->tauxRealisation(),
                'source_donnee' => $realisation?->source_donnee,
                'saisi' => $realisation !== null,
            ];
        });
    }
}
