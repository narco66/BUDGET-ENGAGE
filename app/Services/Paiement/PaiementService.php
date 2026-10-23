<?php

namespace App\Services\Paiement;

use App\Enums\ModeCreationPaiement;
use App\Enums\StatutPaiement;
use App\Models\LigneBudgetaire;
use App\Models\Ordonnancement;
use App\Models\Paiement;
use App\Models\PaiementRejet;
use App\Models\User;
use App\Services\EngagementExecutionService;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Orchestrateur du cycle de vie du paiement ERP : création (auto / manuelle /
 * partielle / groupée / rectificatif / annulation), mise à jour, transmission
 * bancaire, exécution (impact ledger montant_paye + recalcul statut engagement +
 * écritures comptables), rejet, rapprochement, clôture et annulation.
 * montant_paye (en-tête) reste à 0 tant que le paiement n'est pas exécuté.
 */
class PaiementService
{
    private const SCALE = 2;

    public function __construct(
        private readonly PaiementCalculationService $calcul,
        private readonly PaiementValidationService $validation,
        private readonly PaiementTreasuryService $treasury,
        private readonly PaiementBankingService $banking,
        private readonly PaiementCurrencyService $currency,
        private readonly PaiementAccountingService $accounting,
        private readonly PaiementGedService $ged,
        private readonly PaiementVersionService $versions,
        private readonly PaiementAuditService $audit,
        private readonly EngagementExecutionService $execution,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function creer(array $data, User $user): Paiement
    {
        $ordonnancement = Ordonnancement::query()->with('liquidation.engagement')->findOrFail((int) $data['ordonnancement_id']);
        $mode = ModeCreationPaiement::from($data['mode_creation'] ?? ModeCreationPaiement::Manuel->value);

        if ($mode->exigeJustification() && empty($data['justification'])) {
            throw ValidationException::withMessages([
                'justification' => __('Une justification est obligatoire pour ce mode de création.'),
            ]);
        }

        return DB::transaction(function () use ($data, $ordonnancement, $mode, $user): Paiement {
            $engagement = $ordonnancement->liquidation?->engagement;

            $paiement = Paiement::query()->create([
                'ordonnancement_id' => $ordonnancement->id,
                'engagement_imputation_id' => $ordonnancement->engagement_imputation_id,
                'paiement_type_id' => $data['paiement_type_id'] ?? null,
                'parent_paiement_id' => $data['parent_paiement_id'] ?? null,
                'paiement_lot_id' => $data['paiement_lot_id'] ?? null,
                'paiement_banque_id' => $data['paiement_banque_id'] ?? null,
                'exercice_budgetaire_id' => $ordonnancement->exercice_budgetaire_id ?? $engagement?->exercice_budgetaire_id,
                'fournisseur_id' => $data['fournisseur_id'] ?? $ordonnancement->fournisseur_id,
                'beneficiaire_type' => $data['beneficiaire_type'] ?? $ordonnancement->beneficiaire_type?->value,
                'beneficiaire_id' => $data['beneficiaire_id'] ?? $ordonnancement->beneficiaire_id,
                'numero' => $this->generateUniqueNumero((int) ($engagement?->exerciceBudgetaire?->annee ?? now()->year)),
                'mode_creation' => $mode,
                'statut' => StatutPaiement::Brouillon,
                'objet' => $data['objet'] ?? $ordonnancement->objet,
                'devise' => $data['devise'] ?? 'XAF',
                'devise_paiement' => $data['devise_paiement'] ?? null,
                'taux_change' => $data['taux_change'] ?? null,
                'frais_bancaires' => $data['frais_bancaires'] ?? 0,
                'justification' => $data['justification'] ?? null,
                'mode_paiement' => $data['mode_paiement'] ?? null,
                'priorite' => $data['priorite'] ?? null,
                'date_prevue' => $data['date_prevue'] ?? null,
                'date_paiement' => $data['date_paiement'] ?? now()->toDateString(),
                'montant_paye' => 0,
                'reference_paiement' => $data['reference_paiement'] ?? null,
                'commentaire' => $data['commentaire'] ?? null,
                'created_by' => $user->id,
            ]);

            $this->remplacerLignes($paiement, $data['lignes'] ?? []);
            $this->calcul->recompute($paiement, $user);
            $this->currency->appliquer($paiement->fresh(['lignes']), $user);
            $this->ged->heriterDepuisOrdonnancement($paiement, $user);

            $this->audit->record($paiement, $user, 'creation', 'Création du paiement ('.$mode->value.').');

            return $paiement->fresh(['lignes']);
        });
    }

    /**
     * Génération automatique depuis un ordonnancement mandaté : une ligne par
     * ligne d'ordonnancement, à hauteur du reste à payer.
     */
    public function genererDepuisOrdonnancement(Ordonnancement $ordonnancement, User $user, array $data = []): Paiement
    {
        $ordonnancement->loadMissing('lignes');

        if ($ordonnancement->lignes->isEmpty()) {
            throw ValidationException::withMessages([
                'ordonnancement' => __('L’ordonnancement ne comporte aucune ligne à payer.'),
            ]);
        }

        $lignes = [];
        foreach ($ordonnancement->lignes as $ligne) {
            if (bccomp((string) $ligne->montant_ordonnance, '0', self::SCALE) <= 0) {
                continue;
            }

            $lignes[] = [
                'ordonnancement_ligne_id' => $ligne->id,
                'engagement_imputation_id' => $ligne->engagement_imputation_id,
                'ligne_budgetaire_id' => $ligne->ligne_budgetaire_id,
                'source_financement_id' => $ligne->source_financement_id,
                'activite_id' => $ligne->activite_id,
                'centre_cout' => $ligne->centre_cout,
                'centre_responsabilite' => $ligne->centre_responsabilite,
                'designation' => $ligne->designation,
                'montant_ordonnance' => $ligne->montant_ordonnance,
                'montant_paye' => $ligne->montant_ordonnance,
                'mode_paiement' => $ligne->mode_paiement,
            ];
        }

        if ($lignes === []) {
            throw ValidationException::withMessages(['ordonnancement' => __('Rien à payer sur cet ordonnancement.')]);
        }

        return $this->creer(array_merge($data, [
            'ordonnancement_id' => $ordonnancement->id,
            'mode_creation' => ModeCreationPaiement::Automatique->value,
            'lignes' => $lignes,
        ]), $user);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function mettreAJour(Paiement $paiement, array $data, User $user): Paiement
    {
        if (! ($paiement->statut?->estModifiable() ?? true)) {
            throw ValidationException::withMessages([
                'statut' => __('Seul un paiement en brouillon ou retourné est modifiable.'),
            ]);
        }

        return DB::transaction(function () use ($paiement, $data, $user): Paiement {
            $this->versions->snapshot($paiement, $user, $data['motif'] ?? null);

            $paiement->update(array_filter([
                'paiement_type_id' => $data['paiement_type_id'] ?? $paiement->paiement_type_id,
                'paiement_banque_id' => $data['paiement_banque_id'] ?? $paiement->paiement_banque_id,
                'fournisseur_id' => $data['fournisseur_id'] ?? $paiement->fournisseur_id,
                'objet' => $data['objet'] ?? $paiement->objet,
                'devise' => $data['devise'] ?? $paiement->devise,
                'devise_paiement' => $data['devise_paiement'] ?? $paiement->devise_paiement,
                'taux_change' => $data['taux_change'] ?? $paiement->taux_change,
                'frais_bancaires' => $data['frais_bancaires'] ?? $paiement->frais_bancaires,
                'mode_paiement' => $data['mode_paiement'] ?? $paiement->mode_paiement,
                'priorite' => $data['priorite'] ?? $paiement->priorite,
                'date_prevue' => $data['date_prevue'] ?? $paiement->date_prevue,
                'date_paiement' => $data['date_paiement'] ?? $paiement->date_paiement,
                'reference_paiement' => $data['reference_paiement'] ?? $paiement->reference_paiement,
                'commentaire' => $data['commentaire'] ?? $paiement->commentaire,
            ], fn ($v) => $v !== null));

            if (array_key_exists('lignes', $data)) {
                $this->remplacerLignes($paiement, $data['lignes']);
            }

            $this->calcul->recompute($paiement, $user);
            $this->currency->appliquer($paiement->fresh(['lignes']), $user);
            $this->audit->record($paiement, $user, 'modification', 'Modification du paiement.');

            return $paiement->fresh(['lignes']);
        });
    }

    /**
     * Préparation/transmission à la banque (Validé → Transmis banque).
     */
    public function transmettreBanque(Paiement $paiement, User $user, bool $derogationTresorerie = false): Paiement
    {
        if (! ($paiement->statut?->estTransmissibleBanque() ?? false)) {
            throw ValidationException::withMessages([
                'statut' => __('Seul un paiement validé peut être transmis à la banque.'),
            ]);
        }

        return DB::transaction(function () use ($paiement, $user, $derogationTresorerie): Paiement {
            Paiement::query()->whereKey($paiement->id)->lockForUpdate()->firstOrFail();
            $paiement->load(['lignes', 'type']);

            $this->validation->assertBanqueRequise($paiement);
            if ($paiement->paiement_banque_id !== null) {
                $this->banking->assertBanqueValide($paiement->banque);
            }
            $this->treasury->assertDecaissementPossible($paiement, $this->calcul->totalAPayer($paiement), $derogationTresorerie);

            $paiement->update([
                'statut' => StatutPaiement::TransmisBanque,
                'transmis_banque_at' => now(),
                'transmis_banque_par' => $user->id,
                'reference_bancaire' => $paiement->reference_bancaire ?: $this->banking->genererReferenceBancaire($paiement),
                'montant_transmis_banque' => $this->calcul->totalAPayer($paiement),
            ]);

            $this->audit->record($paiement, $user, 'transmission_banque', 'Transmission à la banque.', [
                'reference_bancaire' => $paiement->fresh()->reference_bancaire,
            ]);

            return $paiement->fresh();
        });
    }

    /**
     * Exécution bancaire (décaissement) : impacte le ledger budgétaire
     * (montant_paye), recalcule le statut de l'engagement (→ Payé), génère les
     * écritures comptables. montant_paye (en-tête) ← total à payer.
     */
    public function executer(Paiement $paiement, User $user, bool $derogationTresorerie = false): Paiement
    {
        if (! ($paiement->statut?->estExecutable() ?? false)) {
            throw ValidationException::withMessages([
                'statut' => __('Seul un paiement transmis à la banque peut être exécuté.'),
            ]);
        }

        return DB::transaction(function () use ($paiement, $user, $derogationTresorerie): Paiement {
            Paiement::query()->whereKey($paiement->id)->lockForUpdate()->firstOrFail();
            $paiement->load(['lignes', 'ordonnancement.liquidation.engagement']);

            $this->validation->assertPretPourExecution($paiement, $derogationTresorerie);

            $total = $this->calcul->totalAPayer($paiement);

            foreach ($this->parLigneBudgetaire($paiement) as $ligneBudgetaireId => $montant) {
                $ligne = LigneBudgetaire::query()->whereKey($ligneBudgetaireId)->lockForUpdate()->firstOrFail();
                $ligne->update([
                    'montant_paye' => bcadd((string) $ligne->montant_paye, $montant, self::SCALE),
                ]);
            }

            $numero = (string) $paiement->numero;
            $payload = $paiement->id.'|'.$numero.'|'.$total.'|'.now()->toIso8601String();

            $paiement->update([
                'montant_paye' => $total,
                'statut' => StatutPaiement::Execute,
                'execute_at' => now(),
                'executed_by' => $user->id,
                'date_valeur' => $paiement->date_valeur ?? now()->toDateString(),
                'preuve_numero' => 'PREUVE-'.$numero,
                'quittance_numero' => 'QUIT-'.$numero,
                'preuve_hash' => hash_hmac('sha256', $payload, (string) config('app.key')),
            ]);

            $this->accounting->genererEcritures($paiement->fresh(), $user);

            $engagement = $paiement->ordonnancement?->liquidation?->engagement;
            if ($engagement !== null) {
                $this->execution->recalculateExecutionStatut($engagement->fresh(), $user);
            }

            $this->audit->record($paiement, $user, 'execution', 'Exécution bancaire du paiement.', [
                'montant' => $total,
                'preuve' => $paiement->fresh()->preuve_numero,
            ]);

            $id = $paiement->id;
            DB::afterCommit(function () use ($user, $id, $numero, $total): void {
                BudgetJournal::record($user, "Exécution du paiement {$numero} (montant {$total}).", Paiement::find($id), [
                    'paiement_id' => $id,
                    'montant' => $total,
                ]);
            });

            return $paiement->fresh();
        });
    }

    /**
     * Signale un rejet bancaire : crée la trace de rejet et bascule le paiement
     * en échec pour reprise.
     *
     * @param  array<string, mixed>  $data
     */
    public function signalerRejet(Paiement $paiement, array $data, User $user): PaiementRejet
    {
        return DB::transaction(function () use ($paiement, $data, $user): PaiementRejet {
            $rejet = $paiement->rejets()->create([
                'motif' => $data['motif'],
                'description' => $data['description'] ?? null,
                'date_rejet' => $data['date_rejet'] ?? now()->toDateString(),
                'reference_rejet' => $data['reference_rejet'] ?? null,
                'created_by' => $user->id,
            ]);

            $paiement->update(['statut' => StatutPaiement::Echoue]);

            $this->audit->record($paiement, $user, 'rejet', 'Rejet bancaire enregistré.', [
                'motif' => $data['motif'],
                'rejet_id' => $rejet->id,
            ]);

            return $rejet;
        });
    }

    public function rapprocher(Paiement $paiement, User $user): Paiement
    {
        if (! ($paiement->statut?->estRapprochable() ?? false)) {
            throw ValidationException::withMessages([
                'statut' => __('Seul un paiement exécuté peut être rapproché.'),
            ]);
        }

        $paiement->update(['statut' => StatutPaiement::Rapproche, 'rapproche_at' => now()]);
        $this->audit->record($paiement, $user, 'rapprochement', 'Paiement rapproché.');

        return $paiement->fresh();
    }

    public function cloturer(Paiement $paiement, User $user): Paiement
    {
        if (! ($paiement->statut?->estCloturable() ?? false)) {
            throw ValidationException::withMessages([
                'statut' => __('Seul un paiement rapproché peut être clôturé.'),
            ]);
        }

        $paiement->update(['statut' => StatutPaiement::Cloture, 'cloture_at' => now()]);
        $this->audit->record($paiement, $user, 'cloture', 'Paiement clôturé.');

        return $paiement->fresh();
    }

    public function annuler(Paiement $paiement, User $user, ?string $motif = null): void
    {
        DB::transaction(function () use ($paiement, $user, $motif): void {
            Paiement::query()->whereKey($paiement->id)->lockForUpdate()->firstOrFail();
            $paiement->load(['lignes', 'ordonnancement.liquidation.engagement']);

            $etaitExecute = in_array($paiement->statut, [
                StatutPaiement::Execute, StatutPaiement::Rapproche, StatutPaiement::Cloture,
            ], true);

            if ($etaitExecute) {
                foreach ($this->parLigneBudgetaire($paiement) as $ligneBudgetaireId => $montant) {
                    $ligne = LigneBudgetaire::query()->whereKey($ligneBudgetaireId)->lockForUpdate()->firstOrFail();
                    $ligne->update([
                        'montant_paye' => bcsub((string) $ligne->montant_paye, $montant, self::SCALE),
                    ]);
                }
                $paiement->ecritures()->delete();
            }

            $engagement = $paiement->ordonnancement?->liquidation?->engagement;

            $paiement->update([
                'statut' => StatutPaiement::Annule,
                'cancelled_at' => now(),
                'cancelled_by' => $user->id,
                'motif_annulation' => $motif,
            ]);
            $paiement->delete();

            if ($etaitExecute && $engagement !== null) {
                $this->execution->recalculateExecutionStatut($engagement->fresh(), $user);
            }

            $this->audit->record($paiement, $user, 'annulation', 'Annulation du paiement.', ['motif' => $motif]);

            $id = $paiement->id;
            $numero = (string) $paiement->numero;
            DB::afterCommit(function () use ($user, $id, $numero): void {
                BudgetJournal::record($user, "Annulation du paiement {$numero}.", Paiement::withTrashed()->find($id), [
                    'paiement_id' => $id,
                ]);
            });
        });
    }

    public function generateUniqueNumero(int $annee): string
    {
        return DB::transaction(function () use ($annee): string {
            do {
                $numero = sprintf('PAY-%d-%s', $annee, strtoupper(Str::random(8)));
            } while (Paiement::withTrashed()->where('numero', $numero)->exists());

            return $numero;
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $lignes
     */
    private function remplacerLignes(Paiement $paiement, array $lignes): void
    {
        $paiement->lignes()->delete();

        foreach (array_values($lignes) as $ordre => $ligne) {
            $paiement->lignes()->create([
                'ordonnancement_ligne_id' => $ligne['ordonnancement_ligne_id'] ?? null,
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
                'montant_ordonnance' => $ligne['montant_ordonnance'] ?? 0,
                'montant_paye' => $ligne['montant_paye'] ?? 0,
                'devise' => $ligne['devise'] ?? ($paiement->devise ?? 'XAF'),
                'taux_change' => $ligne['taux_change'] ?? null,
                'frais_bancaires' => $ligne['frais_bancaires'] ?? 0,
                'reference_bancaire' => $ligne['reference_bancaire'] ?? null,
                'observation' => $ligne['observation'] ?? null,
                'ordre' => $ordre + 1,
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    private function parLigneBudgetaire(Paiement $paiement): array
    {
        $map = [];
        foreach ($paiement->lignes as $ligne) {
            $lb = $ligne->ligne_budgetaire_id;
            if ($lb === null) {
                continue;
            }
            $map[$lb] = bcadd($map[$lb] ?? '0', (string) $ligne->montant_paye, self::SCALE);
        }

        return $map;
    }
}
