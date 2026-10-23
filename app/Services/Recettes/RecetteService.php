<?php

namespace App\Services\Recettes;

use App\Enums\StatutRecettePerception;
use App\Models\BudgetLigne;
use App\Models\ExerciceBudgetaire;
use App\Models\RecettePerception;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecetteService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function enregistrer(array $data, User $user, bool $valider = false): RecettePerception
    {
        return DB::transaction(function () use ($data, $user, $valider): RecettePerception {
            $exercice = ExerciceBudgetaire::query()->findOrFail($data['exercice_budgetaire_id']);

            $perception = RecettePerception::query()->create([
                ...$data,
                'numero' => $this->genererNumero($exercice),
                'statut' => $valider ? StatutRecettePerception::Valide : StatutRecettePerception::Brouillon,
                'created_by' => $user->id,
                'validated_by' => $valider ? $user->id : null,
                'validated_at' => $valider ? now() : null,
            ]);

            if ($valider) {
                $this->recalculerLigneRecette($perception);
            }

            return $perception;
        });
    }

    public function valider(RecettePerception $perception, User $user): RecettePerception
    {
        abort_if($perception->statut !== StatutRecettePerception::Brouillon, 422);

        return DB::transaction(function () use ($perception, $user): RecettePerception {
            $perception->update([
                'statut' => StatutRecettePerception::Valide,
                'validated_by' => $user->id,
                'validated_at' => now(),
            ]);

            $this->recalculerLigneRecette($perception->fresh());

            return $perception->fresh();
        });
    }

    public function annuler(RecettePerception $perception, User $user, string $motif): RecettePerception
    {
        abort_if($perception->statut === StatutRecettePerception::Annule, 422);

        return DB::transaction(function () use ($perception, $user, $motif): RecettePerception {
            $etaitValide = $perception->statut === StatutRecettePerception::Valide;

            $perception->update([
                'statut' => StatutRecettePerception::Annule,
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'motif_annulation' => $motif,
            ]);

            if ($etaitValide && $perception->budget_ligne_id) {
                $this->recalculerCumulLigne((int) $perception->budget_ligne_id);
            }

            return $perception->fresh();
        });
    }

    /**
     * @return array<string, float|int>
     */
    public function syntheseExercice(ExerciceBudgetaire $exercice): array
    {
        $prevu = (float) BudgetLigne::query()
            ->where('exercice_budgetaire_id', $exercice->id)
            ->where('section', 'recette')
            ->where('niveau', 'ligne')
            ->sum('montant_total');

        $percu = (float) RecettePerception::query()
            ->where('exercice_budgetaire_id', $exercice->id)
            ->where('statut', StatutRecettePerception::Valide)
            ->sum('montant');

        return [
            'nb_lignes_recette' => BudgetLigne::query()
                ->where('exercice_budgetaire_id', $exercice->id)
                ->where('section', 'recette')
                ->where('niveau', 'ligne')
                ->count(),
            'montant_prevu' => $prevu,
            'montant_percu' => $percu,
            'ecart' => $prevu - $percu,
            'taux_realisation' => $prevu > 0 ? round($percu / $prevu * 100, 1) : 0.0,
            'nb_perceptions' => RecettePerception::query()
                ->where('exercice_budgetaire_id', $exercice->id)
                ->where('statut', StatutRecettePerception::Valide)
                ->count(),
        ];
    }

    /**
     * Rapprochement prévu / perçu par ligne de recette.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function rapprochement(ExerciceBudgetaire $exercice): Collection
    {
        $lignes = BudgetLigne::query()
            ->where('exercice_budgetaire_id', $exercice->id)
            ->where('section', 'recette')
            ->where('niveau', 'ligne')
            ->orderBy('code')
            ->get();

        $percuParLigne = RecettePerception::query()
            ->where('exercice_budgetaire_id', $exercice->id)
            ->where('statut', StatutRecettePerception::Valide)
            ->whereNotNull('budget_ligne_id')
            ->selectRaw('budget_ligne_id, SUM(montant) as total')
            ->groupBy('budget_ligne_id')
            ->pluck('total', 'budget_ligne_id');

        return $lignes->map(function (BudgetLigne $ligne) use ($percuParLigne): array {
            $prevu = (float) $ligne->montant_total;
            $percu = (float) ($percuParLigne[$ligne->id] ?? 0);

            return [
                'id' => $ligne->id,
                'code' => $ligne->code,
                'libelle' => $ligne->libelle,
                'montant_prevu' => $prevu,
                'montant_percu' => $percu,
                'ecart' => $prevu - $percu,
                'taux_realisation' => $prevu > 0 ? round($percu / $prevu * 100, 1) : 0.0,
            ];
        });
    }

    private function recalculerLigneRecette(RecettePerception $perception): void
    {
        if ($perception->budget_ligne_id === null) {
            return;
        }

        $this->recalculerCumulLigne((int) $perception->budget_ligne_id);
    }

    private function recalculerCumulLigne(int $budgetLigneId): void
    {
        $ligne = BudgetLigne::query()->findOrFail($budgetLigneId);
        $percu = (float) RecettePerception::query()
            ->where('budget_ligne_id', $budgetLigneId)
            ->where('statut', StatutRecettePerception::Valide)
            ->sum('montant');

        $prevu = (float) $ligne->montant_total;

        $ligne->update([
            'montant_realise' => $percu,
            'taux_realisation' => $prevu > 0 ? round($percu / $prevu * 100, 2) : 0,
            'variation' => $percu - $prevu,
        ]);
    }

    private function genererNumero(ExerciceBudgetaire $exercice): string
    {
        $seq = RecettePerception::query()
            ->where('exercice_budgetaire_id', $exercice->id)
            ->withTrashed()
            ->count() + 1;

        return sprintf('REC-%d-%04d', $exercice->annee, $seq);
    }
}
