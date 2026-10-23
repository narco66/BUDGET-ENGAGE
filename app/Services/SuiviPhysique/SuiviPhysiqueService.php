<?php

namespace App\Services\SuiviPhysique;

use App\Enums\StatutSuiviRealisation;
use App\Models\Activite;
use App\Models\ExerciceBudgetaire;
use App\Models\SuiviPhysiqueRealisation;
use App\Models\User;
use Illuminate\Support\Collection;

class SuiviPhysiqueService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function enregistrer(array $data, User $user): SuiviPhysiqueRealisation
    {
        return SuiviPhysiqueRealisation::query()->create([
            ...$data,
            'statut' => StatutSuiviRealisation::Brouillon,
            'created_by' => $user->id,
        ]);
    }

    public function valider(SuiviPhysiqueRealisation $realisation, User $user): SuiviPhysiqueRealisation
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
    public function syntheseExercice(ExerciceBudgetaire $exercice): array
    {
        $query = SuiviPhysiqueRealisation::query()
            ->where('exercice_budgetaire_id', $exercice->id)
            ->where('statut', StatutSuiviRealisation::Valide);

        $count = (clone $query)->count();
        $avg = $count > 0 ? (float) (clone $query)->avg('taux_avancement') : 0.0;

        return [
            'nb_realisations' => $count,
            'taux_avancement_moyen' => round($avg, 1),
            'activites_suivies' => (clone $query)->distinct('activite_id')->count('activite_id'),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function parActivite(ExerciceBudgetaire $exercice, ?int $trimestre = null): Collection
    {
        $query = SuiviPhysiqueRealisation::query()
            ->with('activite')
            ->where('exercice_budgetaire_id', $exercice->id)
            ->where('statut', StatutSuiviRealisation::Valide);

        if ($trimestre !== null) {
            $query->where('trimestre', $trimestre);
        }

        return $query->get()
            ->groupBy('activite_id')
            ->map(function (Collection $group): array {
                /** @var SuiviPhysiqueRealisation $latest */
                $latest = $group->sortByDesc('periode_fin')->first();
                $activite = $latest->activite;

                return [
                    'activite_id' => $activite?->id,
                    'code' => $activite?->code,
                    'libelle' => $activite?->libelle,
                    'taux_avancement' => (float) $group->avg('taux_avancement'),
                    'nb_saisies' => $group->count(),
                    'derniere_periode' => $latest->periode_fin?->format('d/m/Y'),
                ];
            })
            ->sortBy('code')
            ->values();
    }
}
