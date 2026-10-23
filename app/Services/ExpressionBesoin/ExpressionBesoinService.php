<?php

namespace App\Services\ExpressionBesoin;

use App\Enums\StatutBesoin;
use App\Models\ExpressionBesoin;
use App\Models\ExerciceBudgetaire;
use App\Models\User;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Gestion du cycle de saisie d'une expression de besoin : génération de la
 * référence, calcul du montant total, persistance des lignes, journalisation.
 */
class ExpressionBesoinService
{
    public function __construct(private readonly ExpressionBesoinAuditService $audit)
    {
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $lignes
     */
    public function create(array $data, array $lignes, User $user): ExpressionBesoin
    {
        return DB::transaction(function () use ($data, $lignes, $user): ExpressionBesoin {
            $exercice = ExerciceBudgetaire::query()->findOrFail($data['exercice_budgetaire_id']);
            $lignes = $this->normalizeLignes($lignes);

            $data['reference'] = $this->generateReference((int) $exercice->annee);
            $data['statut'] = StatutBesoin::Brouillon->value;
            $data['montant_total'] = $this->totalLignes($lignes);
            $data['demandeur_id'] = $data['demandeur_id'] ?? $user->id;
            $data['created_by'] = $user->id;

            /** @var ExpressionBesoin $besoin */
            $besoin = ExpressionBesoin::query()->create($data);
            $besoin->lignes()->createMany($lignes);

            BudgetJournal::record($user, "Création expression de besoin {$besoin->reference}.", $besoin, [
                'expression_besoin_id' => $besoin->id,
                'type_besoin' => $besoin->type_besoin->value,
            ]);
            $this->audit->record($besoin, $user, 'creation', 'Création du besoin.', [
                'type_besoin' => $besoin->type_besoin->value,
                'montant' => (string) $besoin->montant_total,
            ]);

            return $besoin;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $lignes
     */
    public function update(ExpressionBesoin $besoin, array $data, array $lignes, User $user): ExpressionBesoin
    {
        return DB::transaction(function () use ($besoin, $data, $lignes, $user): ExpressionBesoin {
            $lignes = $this->normalizeLignes($lignes);

            $data['montant_total'] = $this->totalLignes($lignes);
            $data['updated_by'] = $user->id;

            $besoin->update($data);

            // Remplacement intégral des lignes (soft-deletes conservés en historique).
            $besoin->lignes()->delete();
            $besoin->lignes()->createMany($lignes);

            BudgetJournal::record($user, "Modification expression de besoin {$besoin->reference}.", $besoin, [
                'expression_besoin_id' => $besoin->id,
            ]);
            $this->audit->record($besoin, $user, 'modification', 'Modification du besoin.');

            return $besoin->fresh();
        });
    }

    /**
     * Met un besoin validé en révision contrôlée : retour en brouillon, circuit
     * de validation réinitialisé, motif tracé et numéro de révision incrémenté.
     */
    public function revise(ExpressionBesoin $besoin, string $motif, User $user): ExpressionBesoin
    {
        if ($besoin->statut !== StatutBesoin::Valide) {
            throw ValidationException::withMessages(['revision' => __('Seul un besoin validé peut être mis en révision.')]);
        }

        if ($besoin->engagement_id !== null) {
            throw ValidationException::withMessages(['revision' => __('Un besoin déjà converti en engagement ne peut pas être révisé.')]);
        }

        return DB::transaction(function () use ($besoin, $motif, $user): ExpressionBesoin {
            $besoin->validations()->delete();

            $besoin->update([
                'statut' => StatutBesoin::Brouillon->value,
                'version_revision' => (int) $besoin->version_revision + 1,
                'derniere_revision_at' => now(),
                'derniere_revision_motif' => $motif,
                'submitted_at' => null,
                'validated_at' => null,
                'updated_by' => $user->id,
            ]);

            BudgetJournal::record($user, "Mise en révision du besoin {$besoin->reference} (révision v{$besoin->version_revision}).", $besoin, [
                'expression_besoin_id' => $besoin->id,
            ]);
            $this->audit->record($besoin, $user, 'revision', "Mise en révision (motif : {$motif}).", [
                'version' => $besoin->version_revision,
                'motif' => $motif,
            ]);

            return $besoin->fresh();
        });
    }

    public function generateReference(int $annee): string
    {
        $prefixe = sprintf('EB-%d-', $annee);

        $seq = ExpressionBesoin::withTrashed()
            ->where('reference', 'like', $prefixe.'%')
            ->count() + 1;

        do {
            $reference = $prefixe.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            $seq++;
        } while (ExpressionBesoin::withTrashed()->where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * @param  array<int, array<string, mixed>>  $lignes
     * @return array<int, array<string, mixed>>
     */
    private function normalizeLignes(array $lignes): array
    {
        $result = [];

        foreach ($lignes as $ligne) {
            $designation = trim((string) ($ligne['designation'] ?? ''));
            if ($designation === '') {
                continue;
            }

            $quantite = (float) ($ligne['quantite'] ?? 0);
            $prix = (float) ($ligne['prix_unitaire'] ?? 0);

            $result[] = [
                'designation' => $designation,
                'code_article' => $ligne['code_article'] ?? null,
                'categorie' => $ligne['categorie'] ?? null,
                'specifications' => $ligne['specifications'] ?? null,
                'quantite' => $quantite,
                'unite' => $ligne['unite'] ?? null,
                'prix_unitaire' => $prix,
                'montant' => round($quantite * $prix, 2),
                'priorite' => $ligne['priorite'] ?? null,
                'echeance' => filled($ligne['echeance'] ?? null) ? $ligne['echeance'] : null,
                'ligne_budgetaire_id' => $ligne['ligne_budgetaire_id'] ?? null,
                'source_financement_id' => $ligne['source_financement_id'] ?? null,
                'activite_id' => $ligne['activite_id'] ?? null,
                'observation' => $ligne['observation'] ?? null,
            ];
        }

        return $result;
    }

    /**
     * @param  array<int, array<string, mixed>>  $lignes
     */
    private function totalLignes(array $lignes): string
    {
        $total = '0';
        foreach ($lignes as $ligne) {
            $total = bcadd($total, (string) ($ligne['montant'] ?? 0), 2);
        }

        return $total;
    }
}
