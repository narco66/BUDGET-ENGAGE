<?php

namespace App\Services\ExpressionBesoin;

use App\Enums\BeneficiaireType;
use App\Enums\StatutBesoin;
use App\Enums\StatutEngagement;
use App\Models\Engagement;
use App\Models\ExpressionBesoin;
use App\Models\User;
use App\Services\Budget\CreditAvailabilityService;
use App\Support\BudgetJournal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Conversion d'une expression de besoin validée en Engagement.
 *
 * L'engagement est créé en Brouillon avec ses imputations, puis suit le circuit
 * d'engagement existant qui réservera réellement le crédit à l'approbation
 * (invariant budgétaire préservé). La double conversion est interdite.
 */
class ExpressionBesoinToEngagementService
{
    public function __construct(
        private readonly CreditAvailabilityService $credits,
        private readonly ExpressionBesoinAuditService $audit,
    ) {
    }

    public function convert(ExpressionBesoin $besoin, User $user): Engagement
    {
        if ($besoin->statut !== StatutBesoin::Valide) {
            throw ValidationException::withMessages(['conversion' => __('Seul un besoin validé peut être converti en engagement.')]);
        }

        if ($besoin->engagement_id !== null) {
            throw ValidationException::withMessages(['conversion' => __('Ce besoin a déjà été converti en engagement.')]);
        }

        $besoin->loadMissing(['lignes', 'gedAttachments', 'exerciceBudgetaire']);

        $imputations = $besoin->lignes
            ->filter(fn ($l) => $l->ligne_budgetaire_id !== null)
            ->groupBy('ligne_budgetaire_id')
            ->map(fn ($groupe) => [
                'ligne_budgetaire_id' => (int) $groupe->first()->ligne_budgetaire_id,
                'montant_impute' => number_format((float) $groupe->sum(fn ($l) => (float) $l->montant), 2, '.', ''),
                'observation' => "Issu du besoin {$besoin->reference}",
            ])
            ->values()
            ->all();

        if ($imputations === []) {
            throw ValidationException::withMessages(['conversion' => __('Aucune ligne budgétaire à imputer.')]);
        }

        // Contrôle de disponibilité prévisionnelle (identique au flux d'engagement).
        $this->credits->assertImputations($imputations, null);

        return DB::transaction(function () use ($besoin, $user, $imputations): Engagement {
            $annee = (int) ($besoin->exerciceBudgetaire?->annee ?? now()->year);

            $total = '0';
            foreach ($imputations as $imp) {
                $total = bcadd($total, (string) $imp['montant_impute'], 2);
            }

            $engagement = Engagement::query()->create([
                'numero' => $this->generateNumero($annee),
                'exercice_budgetaire_id' => $besoin->exercice_budgetaire_id,
                'ligne_budgetaire_id' => $imputations[0]['ligne_budgetaire_id'],
                'departement_id' => $besoin->departement_id,
                'direction_id' => $besoin->direction_id,
                'service_id' => $besoin->service_id,
                'type_engagement' => 'Expression de besoin',
                'mode_creation' => 'generation',
                'beneficiaire_type' => BeneficiaireType::Autre->value,
                'beneficiaire_autre' => $besoin->structure_demandeuse ?: "Issu du besoin {$besoin->reference}",
                'objet' => $besoin->objet,
                'montant' => $total,
                'date_engagement' => now()->toDateString(),
                'statut' => StatutEngagement::Brouillon->value,
                'created_by' => $user->id,
            ]);

            foreach ($imputations as $imp) {
                $engagement->imputations()->create($imp);
            }

            // Reprise des pièces GED du besoin sur l'engagement.
            foreach ($besoin->gedAttachments as $attachment) {
                $engagement->gedAttachments()->firstOrCreate(
                    ['ged_document_id' => $attachment->ged_document_id],
                    ['role' => 'piece_justificative', 'created_by' => $user->id]
                );
            }

            $besoin->update([
                'statut' => StatutBesoin::Converti->value,
                'engagement_id' => $engagement->id,
                'converted_at' => now(),
                'converted_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            BudgetJournal::record($user, "Conversion du besoin {$besoin->reference} en engagement {$engagement->numero}.", $besoin, [
                'expression_besoin_id' => $besoin->id,
                'engagement_id' => $engagement->id,
                'montant' => $total,
            ]);
            $this->audit->record($besoin, $user, 'conversion', "Conversion en engagement {$engagement->numero}.", [
                'engagement_id' => $engagement->id,
                'engagement_numero' => $engagement->numero,
                'montant' => $total,
            ]);

            return $engagement;
        });
    }

    private function generateNumero(int $annee): string
    {
        do {
            $numero = sprintf('ENG-%d-%s', $annee, strtoupper(Str::random(8)));
        } while (Engagement::withTrashed()->where('numero', $numero)->exists());

        return $numero;
    }
}
