<?php

namespace App\Services\Budget;

use App\Models\BudgetLigne;
use App\Models\ExerciceBudgetaire;
use App\Models\JournalAudit;
use App\Models\LigneBudgetaire;
use App\Models\NatureDepense;
use App\Models\SourceFinancement;
use App\Models\User;
use App\Support\BudgetJournal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Genere / synchronise les lignes budgetaires operationnelles (chaine de la depense)
 * a partir des postes executables de la nomenclature budgetaire (feuilles des depenses).
 */
class BudgetSyncService
{
    /**
     * Synchronise les lignes operationnelles pour un exercice.
     *
     * @return array{creations:int, mises_a_jour:int, ignorees:int, montant:float, lignes:int}
     */
    public function synchronize(ExerciceBudgetaire $exercice, User $user, bool $simulation = false): array
    {
        $leaves = $this->executableLeaves($exercice);

        $creations = 0;
        $updates = 0;
        $ignorees = 0;
        $montant = 0.0;

        if ($simulation) {
            $existing = LigneBudgetaire::query()
                ->where('exercice_budgetaire_id', $exercice->id)
                ->pluck('id', 'code');
            foreach ($leaves as $leaf) {
                $montant += (float) $leaf->montant_total;
                $existing->has($leaf->code) ? $updates++ : $creations++;
            }

            return ['creations' => $creations, 'mises_a_jour' => $updates, 'ignorees' => $ignorees, 'montant' => $montant, 'lignes' => $leaves->count()];
        }

        DB::transaction(function () use ($leaves, $exercice, $user, &$creations, &$updates, &$montant): void {
            $natures = [];
            $sources = [];

            foreach ($leaves as $leaf) {
                $montant += (float) $leaf->montant_total;

                $natureKey = $leaf->categorie ?: 'autre';
                $natures[$natureKey] ??= $this->resolveNature($natureKey);

                $sourceKey = $this->sourceKey((float) $leaf->montant_ceeac, (float) $leaf->montant_ptf);
                $sources[$sourceKey] ??= $this->resolveSource($sourceKey);

                $ligne = LigneBudgetaire::query()->firstOrNew([
                    'exercice_budgetaire_id' => $exercice->id,
                    'code' => $leaf->code,
                ]);

                $isNew = ! $ligne->exists;
                $ligne->fill([
                    'nature_depense_id' => $natures[$natureKey]->id,
                    'source_financement_id' => $sources[$sourceKey]->id,
                    'libelle' => Str::limit($leaf->libelle, 250, ''),
                    'montant_vote' => $leaf->montant_total,
                    'montant_revise' => $leaf->montant_total,
                    'statut' => 'active',
                ]);
                // Ne jamais ecraser les montants d'execution existants.
                if ($isNew) {
                    $ligne->montant_engage = 0;
                    $ligne->montant_liquide = 0;
                    $ligne->montant_ordonnance = 0;
                    $ligne->montant_paye = 0;
                }
                $ligne->save();

                if ($leaf->ligne_budgetaire_id !== $ligne->id) {
                    $leaf->ligne_budgetaire_id = $ligne->id;
                    $leaf->save();
                }

                $isNew ? $creations++ : $updates++;
            }

            JournalAudit::query()->create([
                'user_id' => $user->id,
                'action' => 'synchronisation_budget',
                'entite' => 'exercice_budgetaire',
                'entite_id' => $exercice->id,
                'anciennes_valeurs' => null,
                'nouvelles_valeurs' => ['creations' => $creations, 'mises_a_jour' => $updates, 'montant' => $montant],
                'adresse_ip' => null,
                'user_agent' => null,
                'created_at' => now(),
            ]);
        });

        BudgetJournal::record($user, "Synchronisation lignes operationnelles Budget {$exercice->annee}", null, [
            'creations' => $creations,
            'mises_a_jour' => $updates,
            'montant' => $montant,
        ]);

        return ['creations' => $creations, 'mises_a_jour' => $updates, 'ignorees' => $ignorees, 'montant' => $montant, 'lignes' => $leaves->count()];
    }

    /**
     * Feuilles executables des depenses (montant > 0, sans enfant dans la nomenclature).
     *
     * @return Collection<int, BudgetLigne>
     */
    public function executableLeaves(ExerciceBudgetaire $exercice): Collection
    {
        $depenses = BudgetLigne::query()
            ->where('exercice_budgetaire_id', $exercice->id)
            ->where('section', 'depense')
            ->get();

        $parentCodes = $depenses->pluck('parent_code')->filter()->unique()->flip();

        return $depenses
            ->filter(fn (BudgetLigne $l) => ! $parentCodes->has($l->code) && (float) $l->montant_total > 0)
            ->values();
    }

    private function resolveNature(string $categorie): NatureDepense
    {
        $code = 'BUDG-'.strtoupper(substr($categorie, 0, 40));

        return NatureDepense::query()->firstOrCreate(
            ['code' => $code],
            ['libelle' => 'Budget : '.ucfirst(str_replace('_', ' ', $categorie)), 'statut' => 'actif']
        );
    }

    private function sourceKey(float $ceeac, float $ptf): string
    {
        if ($ceeac > 0 && $ptf > 0) {
            return 'MIXTE';
        }
        if ($ptf > 0) {
            return 'PTF';
        }

        return 'CEEAC';
    }

    private function resolveSource(string $key): SourceFinancement
    {
        $labels = ['CEEAC' => 'CEEAC / Etats membres', 'PTF' => 'Partenaires Techniques et Financiers', 'MIXTE' => 'Financement mixte CEEAC/PTF'];

        return SourceFinancement::query()->firstOrCreate(
            ['code' => 'BUDG-'.$key],
            ['libelle' => $labels[$key] ?? $key, 'statut' => 'actif']
        );
    }
}
