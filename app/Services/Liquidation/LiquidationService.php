<?php

namespace App\Services\Liquidation;

use App\Enums\ModeCreationLiquidation;
use App\Enums\StatutLiquidation;
use App\Models\Engagement;
use App\Models\Liquidation;
use App\Models\LigneBudgetaire;
use App\Models\User;
use App\Services\EngagementExecutionService;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Orchestrateur du cycle de vie de la liquidation ERP : création (auto /
 * manuelle / complémentaire / corrective), mise à jour, certification (impact
 * ledger + recalcul statut engagement) et annulation. Le champ canonique
 * montant_liquide reste à 0 tant que la liquidation n'est pas certifiée, puis
 * vaut montant_net — ce qui préserve les sommes de la chaîne d'exécution.
 */
class LiquidationService
{
    private const SCALE = 2;

    public function __construct(
        private readonly LiquidationCalculationService $calcul,
        private readonly LiquidationValidationService $validation,
        private readonly LiquidationGedService $ged,
        private readonly LiquidationVersionService $versions,
        private readonly LiquidationAuditService $audit,
        private readonly EngagementExecutionService $execution,
    ) {
    }

    /**
     * Création manuelle (régularisation, décision, reprise, migration).
     *
     * @param  array<string, mixed>  $data
     */
    public function creer(array $data, User $user): Liquidation
    {
        $engagement = Engagement::query()->findOrFail((int) $data['engagement_id']);
        $mode = ModeCreationLiquidation::from($data['mode_creation'] ?? ModeCreationLiquidation::Manuel->value);

        if ($mode->exigeJustification() && empty($data['justification'])) {
            throw ValidationException::withMessages([
                'justification' => __('Une justification est obligatoire pour ce mode de création.'),
            ]);
        }

        return DB::transaction(function () use ($data, $engagement, $mode, $user): Liquidation {
            $liquidation = Liquidation::query()->create([
                'engagement_id' => $engagement->id,
                'liquidation_type_id' => $data['liquidation_type_id'] ?? null,
                'parent_liquidation_id' => $data['parent_liquidation_id'] ?? null,
                'exercice_budgetaire_id' => $engagement->exercice_budgetaire_id,
                'fournisseur_id' => $data['fournisseur_id'] ?? null,
                'beneficiaire_type' => $data['beneficiaire_type'] ?? $engagement->beneficiaire_type?->value,
                'beneficiaire_id' => $data['beneficiaire_id'] ?? $engagement->beneficiaire_id,
                'numero' => $this->generateUniqueNumero((int) ($engagement->exerciceBudgetaire?->annee ?? now()->year)),
                'mode_creation' => $mode,
                'statut' => StatutLiquidation::Brouillon,
                'objet' => $data['objet'] ?? $engagement->objet,
                'devise' => $data['devise'] ?? 'XAF',
                'justification' => $data['justification'] ?? null,
                'date_liquidation' => $data['date_liquidation'] ?? now()->toDateString(),
                'montant_liquide' => 0,
                'reference' => $data['reference'] ?? null,
                'commentaire' => $data['commentaire'] ?? null,
                'created_by' => $user->id,
            ]);

            $this->remplacerLignes($liquidation, $data['lignes'] ?? []);
            $this->calcul->recompute($liquidation, $user);
            $this->ged->heriterDepuisEngagement($liquidation, $user);

            $this->audit->record($liquidation, $user, 'creation', 'Création de la liquidation ('.$mode->value.').');

            return $liquidation->fresh(['lignes']);
        });
    }

    /**
     * Génération automatique depuis un engagement validé : une ligne par
     * imputation, à hauteur du reste à liquider.
     */
    public function genererDepuisEngagement(Engagement $engagement, User $user, array $data = []): Liquidation
    {
        $engagement->loadMissing('imputations');

        if ($engagement->imputations->isEmpty()) {
            throw ValidationException::withMessages([
                'engagement' => __('L’engagement ne comporte aucune imputation à liquider.'),
            ]);
        }

        $lignes = [];
        foreach ($engagement->imputations as $imputation) {
            $reste = $this->resteImputation($imputation->id, (string) $imputation->montant_impute);
            if (bccomp($reste, '0', self::SCALE) <= 0) {
                continue;
            }

            $lignes[] = [
                'engagement_imputation_id' => $imputation->id,
                'ligne_budgetaire_id' => $imputation->ligne_budgetaire_id,
                'source_financement_id' => $imputation->source_financement_id,
                'activite_id' => $imputation->activite_id,
                'centre_cout' => $imputation->centre_cout,
                'centre_responsabilite' => $imputation->centre_responsabilite,
                'designation' => $engagement->objet,
                'montant_brut' => $reste,
            ];
        }

        if ($lignes === []) {
            throw ValidationException::withMessages([
                'engagement' => __('L’engagement est déjà intégralement liquidé.'),
            ]);
        }

        return $this->creer(array_merge($data, [
            'engagement_id' => $engagement->id,
            'mode_creation' => ModeCreationLiquidation::Automatique->value,
            'lignes' => $lignes,
        ]), $user);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function mettreAJour(Liquidation $liquidation, array $data, User $user): Liquidation
    {
        if (! ($liquidation->statut?->estModifiable() ?? true)) {
            throw ValidationException::withMessages([
                'statut' => __('Seule une liquidation en brouillon ou retournée est modifiable.'),
            ]);
        }

        return DB::transaction(function () use ($liquidation, $data, $user): Liquidation {
            $this->versions->snapshot($liquidation, $user, $data['motif'] ?? null);

            $liquidation->update(array_filter([
                'liquidation_type_id' => $data['liquidation_type_id'] ?? $liquidation->liquidation_type_id,
                'fournisseur_id' => $data['fournisseur_id'] ?? $liquidation->fournisseur_id,
                'objet' => $data['objet'] ?? $liquidation->objet,
                'devise' => $data['devise'] ?? $liquidation->devise,
                'date_liquidation' => $data['date_liquidation'] ?? $liquidation->date_liquidation,
                'reference' => $data['reference'] ?? $liquidation->reference,
                'commentaire' => $data['commentaire'] ?? $liquidation->commentaire,
            ], fn ($v) => $v !== null));

            if (array_key_exists('lignes', $data)) {
                $this->remplacerLignes($liquidation, $data['lignes']);
            }

            $this->calcul->recompute($liquidation, $user);
            $this->audit->record($liquidation, $user, 'modification', 'Modification de la liquidation.');

            return $liquidation->fresh(['lignes']);
        });
    }

    /**
     * Certification : constate la dette, impacte le ledger budgétaire et recalcule
     * le statut d'exécution de l'engagement. montant_liquide ← montant_net.
     */
    public function certifier(Liquidation $liquidation, User $user): Liquidation
    {
        if (! ($liquidation->statut?->estCertifiable() ?? false)) {
            throw ValidationException::withMessages([
                'statut' => __('Seule une liquidation validée peut être certifiée.'),
            ]);
        }

        return DB::transaction(function () use ($liquidation, $user): Liquidation {
            Liquidation::query()->whereKey($liquidation->id)->lockForUpdate()->firstOrFail();
            $liquidation->load(['lignes', 'engagement']);

            $this->validation->assertPretPourCertification($liquidation);

            // Impact ledger : cumul net par ligne budgétaire.
            foreach ($this->netParLigneBudgetaire($liquidation) as $ligneBudgetaireId => $net) {
                $ligne = LigneBudgetaire::query()->whereKey($ligneBudgetaireId)->lockForUpdate()->firstOrFail();
                $ligne->update([
                    'montant_liquide' => bcadd((string) $ligne->montant_liquide, $net, self::SCALE),
                ]);
            }

            $numero = (string) $liquidation->numero;
            $certificatNumero = 'CERT-'.$numero;
            $payload = $liquidation->id.'|'.$numero.'|'.$liquidation->montant_net.'|'.now()->toIso8601String();

            $liquidation->update([
                'montant_liquide' => $liquidation->montant_net,
                'statut' => StatutLiquidation::Certifie,
                'certifie_at' => now(),
                'certificat_numero' => $certificatNumero,
                'certificat_hash' => hash_hmac('sha256', $payload, (string) config('app.key')),
            ]);

            $this->execution->recalculateExecutionStatut($liquidation->engagement->fresh(), $user);

            $this->audit->record($liquidation, $user, 'certification', 'Certification de la liquidation.', [
                'certificat_numero' => $certificatNumero,
                'montant_net' => (string) $liquidation->montant_net,
            ]);

            $liquidationId = $liquidation->id;
            $montant = (string) $liquidation->montant_net;
            DB::afterCommit(function () use ($user, $liquidationId, $numero, $montant): void {
                BudgetJournal::record($user, "Certification de la liquidation {$numero} (net {$montant}).", Liquidation::find($liquidationId), [
                    'liquidation_id' => $liquidationId,
                    'montant_net' => $montant,
                ]);
            });

            return $liquidation->fresh();
        });
    }

    public function annuler(Liquidation $liquidation, User $user, ?string $motif = null): void
    {
        if ($liquidation->ordonnancements()->exists()) {
            throw ValidationException::withMessages([
                'statut' => __('Impossible d’annuler : des ordonnancements existent. Annulez-les d’abord.'),
            ]);
        }

        DB::transaction(function () use ($liquidation, $user, $motif): void {
            Liquidation::query()->whereKey($liquidation->id)->lockForUpdate()->firstOrFail();
            $liquidation->load(['lignes', 'engagement']);

            $etaitCertifiee = $liquidation->statut === StatutLiquidation::Certifie;

            // Reprise du ledger si la liquidation avait déjà impacté le budget.
            if ($etaitCertifiee) {
                foreach ($this->netParLigneBudgetaire($liquidation) as $ligneBudgetaireId => $net) {
                    $ligne = LigneBudgetaire::query()->whereKey($ligneBudgetaireId)->lockForUpdate()->firstOrFail();
                    $ligne->update([
                        'montant_liquide' => bcsub((string) $ligne->montant_liquide, $net, self::SCALE),
                    ]);
                }
            }

            $engagement = $liquidation->engagement;

            $liquidation->update([
                'statut' => StatutLiquidation::Annule,
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'motif_annulation' => $motif,
            ]);
            $liquidation->delete(); // soft delete → exclue des sommes d'exécution

            if ($etaitCertifiee && $engagement !== null) {
                $this->execution->recalculateExecutionStatut($engagement->fresh(), $user);
            }

            $this->audit->record($liquidation, $user, 'annulation', 'Annulation de la liquidation.', ['motif' => $motif]);

            $liquidationId = $liquidation->id;
            $numero = (string) $liquidation->numero;
            DB::afterCommit(function () use ($user, $liquidationId, $numero): void {
                BudgetJournal::record($user, "Annulation de la liquidation {$numero}.", Liquidation::withTrashed()->find($liquidationId), [
                    'liquidation_id' => $liquidationId,
                ]);
            });
        });
    }

    public function generateUniqueNumero(int $annee): string
    {
        return DB::transaction(function () use ($annee): string {
            do {
                $numero = sprintf('LIQ-%d-%s', $annee, strtoupper(Str::random(8)));
            } while (Liquidation::withTrashed()->where('numero', $numero)->exists());

            return $numero;
        });
    }

    /**
     * Remplace l'ensemble des lignes de la liquidation.
     *
     * @param  array<int, array<string, mixed>>  $lignes
     */
    private function remplacerLignes(Liquidation $liquidation, array $lignes): void
    {
        $liquidation->lignes()->delete();

        foreach (array_values($lignes) as $ordre => $ligne) {
            $liquidation->lignes()->create([
                'engagement_imputation_id' => $ligne['engagement_imputation_id'] ?? null,
                'ligne_budgetaire_id' => $ligne['ligne_budgetaire_id'] ?? null,
                'source_financement_id' => $ligne['source_financement_id'] ?? null,
                'activite_id' => $ligne['activite_id'] ?? null,
                'tache_id' => $ligne['tache_id'] ?? null,
                'pap_ligne_budgetaire_id' => $ligne['pap_ligne_budgetaire_id'] ?? null,
                'centre_cout' => $ligne['centre_cout'] ?? null,
                'centre_responsabilite' => $ligne['centre_responsabilite'] ?? null,
                'designation' => $ligne['designation'] ?? '—',
                'quantite_prevue' => $ligne['quantite_prevue'] ?? null,
                'quantite_realisee' => $ligne['quantite_realisee'] ?? null,
                'prix_unitaire' => $ligne['prix_unitaire'] ?? null,
                'montant_brut' => $ligne['montant_brut'] ?? 0,
                'montant_avances' => $ligne['montant_avances'] ?? 0,
                'montant_acomptes' => $ligne['montant_acomptes'] ?? 0,
                'montant_retenues' => $ligne['montant_retenues'] ?? 0,
                'montant_garanties' => $ligne['montant_garanties'] ?? 0,
                'montant_penalites' => $ligne['montant_penalites'] ?? 0,
                'montant_taxes' => $ligne['montant_taxes'] ?? 0,
                'montant_revision_prix' => $ligne['montant_revision_prix'] ?? 0,
                'ordre' => $ordre + 1,
                'observation' => $ligne['observation'] ?? null,
            ]);
        }
    }

    /**
     * Net cumulé par ligne budgétaire (clé = ligne_budgetaire_id).
     *
     * @return array<int, string>
     */
    private function netParLigneBudgetaire(Liquidation $liquidation): array
    {
        $map = [];
        foreach ($liquidation->lignes as $ligne) {
            $lb = $ligne->ligne_budgetaire_id;
            if ($lb === null) {
                continue;
            }
            $map[$lb] = bcadd($map[$lb] ?? '0', (string) $ligne->montant_net, self::SCALE);
        }

        return $map;
    }

    private function resteImputation(int $imputationId, string $montantImpute): string
    {
        $deja = (string) Liquidation::query()
            ->where('engagement_imputation_id', $imputationId)
            ->whereNull('cancelled_at')
            ->sum('montant_liquide');

        return bcsub($montantImpute, $deja, self::SCALE);
    }
}
