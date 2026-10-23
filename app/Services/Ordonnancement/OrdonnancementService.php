<?php

namespace App\Services\Ordonnancement;

use App\Enums\ModeCreationOrdonnancement;
use App\Enums\StatutOrdonnancement;
use App\Models\LigneBudgetaire;
use App\Models\Liquidation;
use App\Models\Ordonnancement;
use App\Models\User;
use App\Services\EngagementExecutionService;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Orchestrateur du cycle de vie de l'ordonnancement ERP : création (auto /
 * manuelle / complémentaire / rectificatif / annulation), mise à jour,
 * génération du mandat (impact ledger montant_ordonnance + recalcul statut
 * engagement) et annulation. montant_ordonnance (en-tête) reste à 0 tant que le
 * mandat n'est pas généré, puis vaut le total ordonnancé — ce qui préserve les
 * sommes de la chaîne d'exécution.
 */
class OrdonnancementService
{
    private const SCALE = 2;

    public function __construct(
        private readonly OrdonnancementCalculationService $calcul,
        private readonly OrdonnancementValidationService $validation,
        private readonly OrdonnancementGedService $ged,
        private readonly OrdonnancementVersionService $versions,
        private readonly OrdonnancementAuditService $audit,
        private readonly EngagementExecutionService $execution,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function creer(array $data, User $user): Ordonnancement
    {
        $liquidation = Liquidation::query()->with('engagement')->findOrFail((int) $data['liquidation_id']);
        $mode = ModeCreationOrdonnancement::from($data['mode_creation'] ?? ModeCreationOrdonnancement::Manuel->value);

        if ($mode->exigeJustification() && empty($data['justification'])) {
            throw ValidationException::withMessages([
                'justification' => __('Une justification est obligatoire pour ce mode de création.'),
            ]);
        }

        return DB::transaction(function () use ($data, $liquidation, $mode, $user): Ordonnancement {
            $engagement = $liquidation->engagement;

            $ordonnancement = Ordonnancement::query()->create([
                'liquidation_id' => $liquidation->id,
                'engagement_imputation_id' => $liquidation->engagement_imputation_id,
                'ordonnancement_type_id' => $data['ordonnancement_type_id'] ?? null,
                'parent_ordonnancement_id' => $data['parent_ordonnancement_id'] ?? null,
                'exercice_budgetaire_id' => $liquidation->exercice_budgetaire_id ?? $engagement?->exercice_budgetaire_id,
                'fournisseur_id' => $data['fournisseur_id'] ?? $liquidation->fournisseur_id,
                'beneficiaire_type' => $data['beneficiaire_type'] ?? $liquidation->beneficiaire_type?->value,
                'beneficiaire_id' => $data['beneficiaire_id'] ?? $liquidation->beneficiaire_id,
                'numero' => $this->generateUniqueNumero((int) ($engagement?->exerciceBudgetaire?->annee ?? now()->year)),
                'mode_creation' => $mode,
                'statut' => StatutOrdonnancement::Brouillon,
                'objet' => $data['objet'] ?? $liquidation->objet ?? $engagement?->objet,
                'devise' => $data['devise'] ?? 'XAF',
                'justification' => $data['justification'] ?? null,
                'date_ordonnancement' => $data['date_ordonnancement'] ?? now()->toDateString(),
                'montant_ordonnance' => 0,
                'reference' => $data['reference'] ?? null,
                'commentaire' => $data['commentaire'] ?? null,
                'created_by' => $user->id,
            ]);

            $this->remplacerLignes($ordonnancement, $data['lignes'] ?? []);
            $this->calcul->recompute($ordonnancement, $user);
            $this->ged->heriterDepuisLiquidation($ordonnancement, $user);

            $this->audit->record($ordonnancement, $user, 'creation', 'Création de l’ordonnancement ('.$mode->value.').');

            return $ordonnancement->fresh(['lignes']);
        });
    }

    /**
     * Génération automatique depuis une liquidation certifiée : une ligne par
     * ligne de liquidation, à hauteur du reste ordonnançable.
     */
    public function genererDepuisLiquidation(Liquidation $liquidation, User $user, array $data = []): Ordonnancement
    {
        $liquidation->loadMissing('lignes');

        if ($liquidation->lignes->isEmpty()) {
            throw ValidationException::withMessages([
                'liquidation' => __('La liquidation ne comporte aucune ligne à ordonnancer.'),
            ]);
        }

        $lignes = [];
        foreach ($liquidation->lignes as $ligne) {
            if (bccomp((string) $ligne->montant_net, '0', self::SCALE) <= 0) {
                continue;
            }

            $lignes[] = [
                'liquidation_ligne_id' => $ligne->id,
                'engagement_imputation_id' => $ligne->engagement_imputation_id,
                'ligne_budgetaire_id' => $ligne->ligne_budgetaire_id,
                'source_financement_id' => $ligne->source_financement_id,
                'activite_id' => $ligne->activite_id,
                'centre_cout' => $ligne->centre_cout,
                'centre_responsabilite' => $ligne->centre_responsabilite,
                'designation' => $ligne->designation,
                'montant_liquide' => $ligne->montant_net,
                'montant_ordonnance' => $ligne->montant_net,
            ];
        }

        if ($lignes === []) {
            throw ValidationException::withMessages(['liquidation' => __('Rien à ordonnancer sur cette liquidation.')]);
        }

        return $this->creer(array_merge($data, [
            'liquidation_id' => $liquidation->id,
            'mode_creation' => ModeCreationOrdonnancement::Automatique->value,
            'lignes' => $lignes,
        ]), $user);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function mettreAJour(Ordonnancement $ordonnancement, array $data, User $user): Ordonnancement
    {
        if (! ($ordonnancement->statut?->estModifiable() ?? true)) {
            throw ValidationException::withMessages([
                'statut' => __('Seul un ordonnancement en brouillon ou retourné est modifiable.'),
            ]);
        }

        return DB::transaction(function () use ($ordonnancement, $data, $user): Ordonnancement {
            $this->versions->snapshot($ordonnancement, $user, $data['motif'] ?? null);

            $ordonnancement->update(array_filter([
                'ordonnancement_type_id' => $data['ordonnancement_type_id'] ?? $ordonnancement->ordonnancement_type_id,
                'fournisseur_id' => $data['fournisseur_id'] ?? $ordonnancement->fournisseur_id,
                'objet' => $data['objet'] ?? $ordonnancement->objet,
                'devise' => $data['devise'] ?? $ordonnancement->devise,
                'date_ordonnancement' => $data['date_ordonnancement'] ?? $ordonnancement->date_ordonnancement,
                'reference' => $data['reference'] ?? $ordonnancement->reference,
                'commentaire' => $data['commentaire'] ?? $ordonnancement->commentaire,
            ], fn ($v) => $v !== null));

            if (array_key_exists('lignes', $data)) {
                $this->remplacerLignes($ordonnancement, $data['lignes']);
            }

            $this->calcul->recompute($ordonnancement, $user);
            $this->audit->record($ordonnancement, $user, 'modification', 'Modification de l’ordonnancement.');

            return $ordonnancement->fresh(['lignes']);
        });
    }

    /**
     * Génération du mandat : valide définitivement, impacte le ledger budgétaire
     * (montant_ordonnance), recalcule le statut de l'engagement, émet le mandat
     * et l'ordre de paiement. montant_ordonnance (en-tête) ← total ordonnancé.
     */
    public function genererMandat(Ordonnancement $ordonnancement, User $user): Ordonnancement
    {
        if (! ($ordonnancement->statut?->estMandatable() ?? false)) {
            throw ValidationException::withMessages([
                'statut' => __('Seul un ordonnancement validé peut faire l’objet d’un mandat.'),
            ]);
        }

        return DB::transaction(function () use ($ordonnancement, $user): Ordonnancement {
            Ordonnancement::query()->whereKey($ordonnancement->id)->lockForUpdate()->firstOrFail();
            $ordonnancement->load(['lignes', 'liquidation.engagement']);

            $this->validation->assertPretPourMandat($ordonnancement);

            $total = $this->calcul->totalAOrdonnancer($ordonnancement);

            foreach ($this->parLigneBudgetaire($ordonnancement) as $ligneBudgetaireId => $montant) {
                $ligne = LigneBudgetaire::query()->whereKey($ligneBudgetaireId)->lockForUpdate()->firstOrFail();
                $ligne->update([
                    'montant_ordonnance' => bcadd((string) $ligne->montant_ordonnance, $montant, self::SCALE),
                ]);
            }

            $numero = (string) $ordonnancement->numero;
            $mandatNumero = 'MAND-'.$numero;
            $payload = $ordonnancement->id.'|'.$numero.'|'.$total.'|'.now()->toIso8601String();
            $hash = hash_hmac('sha256', $payload, (string) config('app.key'));

            $ordonnancement->update([
                'montant_ordonnance' => $total,
                'statut' => StatutOrdonnancement::Mandate,
                'mandate_at' => now(),
                'mandat_numero' => $mandatNumero,
                'visa_numero' => 'VISA-'.$numero,
                'visa_hash' => $hash,
            ]);

            $beneficiaire = $ordonnancement->fournisseur?->raison_sociale;
            $mandat = $ordonnancement->mandats()->create([
                'numero' => $mandatNumero,
                'montant' => $total,
                'date_mandat' => now()->toDateString(),
                'statut' => 'emis',
                'beneficiaire' => $beneficiaire,
                'hash_integrite' => $hash,
                'created_by' => $user->id,
            ]);

            $ordonnancement->ordresPaiement()->create([
                'ordonnancement_mandat_id' => $mandat->id,
                'numero' => 'OP-'.$numero,
                'montant' => $total,
                'date_ordre' => now()->toDateString(),
                'mode_paiement' => $ordonnancement->lignes->first()?->mode_paiement,
                'beneficiaire' => $beneficiaire,
                'statut' => 'emis',
                'created_by' => $user->id,
            ]);

            $engagement = $ordonnancement->liquidation?->engagement;
            if ($engagement !== null) {
                $this->execution->recalculateExecutionStatut($engagement->fresh(), $user);
            }

            $this->audit->record($ordonnancement, $user, 'mandat', 'Génération du mandat et de l’ordre de paiement.', [
                'mandat_numero' => $mandatNumero,
                'montant' => $total,
            ]);

            $id = $ordonnancement->id;
            DB::afterCommit(function () use ($user, $id, $numero, $total): void {
                BudgetJournal::record($user, "Mandat de l’ordonnancement {$numero} (montant {$total}).", Ordonnancement::find($id), [
                    'ordonnancement_id' => $id,
                    'montant' => $total,
                ]);
            });

            return $ordonnancement->fresh();
        });
    }

    public function annuler(Ordonnancement $ordonnancement, User $user, ?string $motif = null): void
    {
        if ($ordonnancement->paiements()->exists()) {
            throw ValidationException::withMessages([
                'statut' => __('Impossible d’annuler : des paiements existent. Annulez-les d’abord.'),
            ]);
        }

        DB::transaction(function () use ($ordonnancement, $user, $motif): void {
            Ordonnancement::query()->whereKey($ordonnancement->id)->lockForUpdate()->firstOrFail();
            $ordonnancement->load(['lignes', 'liquidation.engagement']);

            $etaitMandate = in_array($ordonnancement->statut, [StatutOrdonnancement::Mandate, StatutOrdonnancement::TransmisPaiement], true);

            if ($etaitMandate) {
                foreach ($this->parLigneBudgetaire($ordonnancement) as $ligneBudgetaireId => $montant) {
                    $ligne = LigneBudgetaire::query()->whereKey($ligneBudgetaireId)->lockForUpdate()->firstOrFail();
                    $ligne->update([
                        'montant_ordonnance' => bcsub((string) $ligne->montant_ordonnance, $montant, self::SCALE),
                    ]);
                }
            }

            $engagement = $ordonnancement->liquidation?->engagement;

            $ordonnancement->update([
                'statut' => StatutOrdonnancement::Annule,
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'motif_annulation' => $motif,
            ]);
            $ordonnancement->delete();

            if ($etaitMandate && $engagement !== null) {
                $this->execution->recalculateExecutionStatut($engagement->fresh(), $user);
            }

            $this->audit->record($ordonnancement, $user, 'annulation', 'Annulation de l’ordonnancement.', ['motif' => $motif]);

            $id = $ordonnancement->id;
            $numero = (string) $ordonnancement->numero;
            DB::afterCommit(function () use ($user, $id, $numero): void {
                BudgetJournal::record($user, "Annulation de l’ordonnancement {$numero}.", Ordonnancement::withTrashed()->find($id), [
                    'ordonnancement_id' => $id,
                ]);
            });
        });
    }

    public function generateUniqueNumero(int $annee): string
    {
        return DB::transaction(function () use ($annee): string {
            do {
                $numero = sprintf('ORD-%d-%s', $annee, strtoupper(Str::random(8)));
            } while (Ordonnancement::withTrashed()->where('numero', $numero)->exists());

            return $numero;
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $lignes
     */
    private function remplacerLignes(Ordonnancement $ordonnancement, array $lignes): void
    {
        $ordonnancement->lignes()->delete();

        foreach (array_values($lignes) as $ordre => $ligne) {
            $ordonnancement->lignes()->create([
                'liquidation_ligne_id' => $ligne['liquidation_ligne_id'] ?? null,
                'engagement_imputation_id' => $ligne['engagement_imputation_id'] ?? null,
                'ligne_budgetaire_id' => $ligne['ligne_budgetaire_id'] ?? null,
                'source_financement_id' => $ligne['source_financement_id'] ?? null,
                'activite_id' => $ligne['activite_id'] ?? null,
                'tache_id' => $ligne['tache_id'] ?? null,
                'pap_ligne_budgetaire_id' => $ligne['pap_ligne_budgetaire_id'] ?? null,
                'centre_cout' => $ligne['centre_cout'] ?? null,
                'centre_responsabilite' => $ligne['centre_responsabilite'] ?? null,
                'designation' => $ligne['designation'] ?? '—',
                'montant_liquide' => $ligne['montant_liquide'] ?? 0,
                'montant_ordonnance' => $ligne['montant_ordonnance'] ?? 0,
                'mode_paiement' => $ligne['mode_paiement'] ?? null,
                'echeance' => $ligne['echeance'] ?? null,
                'banque' => $ligne['banque'] ?? null,
                'compte_bancaire' => $ligne['compte_bancaire'] ?? null,
                'observation' => $ligne['observation'] ?? null,
                'ordre' => $ordre + 1,
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function parLigneBudgetaire(Ordonnancement $ordonnancement): array
    {
        $map = [];
        foreach ($ordonnancement->lignes as $ligne) {
            $lb = $ligne->ligne_budgetaire_id;
            if ($lb === null) {
                continue;
            }
            $map[$lb] = bcadd($map[$lb] ?? '0', (string) $ligne->montant_ordonnance, self::SCALE);
        }

        return $map;
    }
}
