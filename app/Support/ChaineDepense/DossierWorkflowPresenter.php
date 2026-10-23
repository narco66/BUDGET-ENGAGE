<?php

namespace App\Support\ChaineDepense;

use App\Models\Engagement;
use App\Models\ExpressionBesoin;
use App\Models\Liquidation;
use App\Models\Ordonnancement;
use App\Models\Paiement;

/**
 * Construit les étapes de la chaîne de la dépense pour l'affichage transversal.
 */
final class DossierWorkflowPresenter
{
    /**
     * @return list<array{
     *     key: string,
     *     label: string,
     *     status: 'done'|'current'|'pending'|'error'|'skipped',
     *     reference: ?string,
     *     url: ?string,
     *     date: ?string,
     *     statut_label: ?string,
     * }>
     */
    public static function fromEngagement(Engagement $engagement): array
    {
        $engagement->loadMissing([
            'expressionBesoin',
            'liquidations.ordonnancements.paiements',
        ]);

        $steps = [];

        if ($besoin = $engagement->expressionBesoin) {
            $steps[] = self::besoinStep($besoin);
        }

        $steps[] = self::engagementStep($engagement);

        $liquidations = $engagement->liquidations;
        if ($liquidations->isEmpty()) {
            $steps[] = self::pendingStep('liquidation', __('Liquidation'));
        } else {
            foreach ($liquidations as $i => $liquidation) {
                $steps[] = self::liquidationStep($liquidation, $liquidations->count() > 1 ? $i + 1 : null);

                $ordonnancements = $liquidation->ordonnancements ?? collect();
                if ($ordonnancements->isEmpty()) {
                    if ($liquidation->statut?->value === 'transmis_ordonnancement' || $liquidation->certificat_numero) {
                        $steps[] = self::pendingStep('ordonnancement-'.$liquidation->id, __('Ordonnancement'));
                    }
                } else {
                    foreach ($ordonnancements as $j => $ordonnancement) {
                        $steps[] = self::ordonnancementStep($ordonnancement, $ordonnancements->count() > 1 ? $j + 1 : null);

                        $paiements = $ordonnancement->paiements ?? collect();
                        if ($paiements->isEmpty()) {
                            $steps[] = self::pendingStep('paiement-'.$ordonnancement->id, __('Paiement'));
                        } else {
                            foreach ($paiements as $k => $paiement) {
                                $steps[] = self::paiementStep($paiement, $paiements->count() > 1 ? $k + 1 : null);
                            }
                        }
                    }
                }
            }
        }

        return self::markCurrent($steps);
    }

    /** @param list<array<string, mixed>> $steps */
    private static function markCurrent(array $steps): array
    {
        $currentIdx = null;
        foreach ($steps as $i => $step) {
            if ($step['status'] === 'current') {
                $currentIdx = $i;
            }
        }
        if ($currentIdx === null) {
            for ($i = count($steps) - 1; $i >= 0; $i--) {
                if ($steps[$i]['status'] === 'done') {
                    if ($i + 1 < count($steps) && $steps[$i + 1]['status'] === 'pending') {
                        $steps[$i + 1]['status'] = 'current';
                    }
                    break;
                }
            }
        }

        return $steps;
    }

    private static function besoinStep(ExpressionBesoin $besoin): array
    {
        $st = StatutPresenter::besoin($besoin->statut);
        $done = in_array($besoin->statut, [
            \App\Enums\StatutBesoin::Valide,
            \App\Enums\StatutBesoin::Converti,
        ], true);
        $error = in_array($besoin->statut, [
            \App\Enums\StatutBesoin::Rejete,
            \App\Enums\StatutBesoin::Annule,
        ], true);

        return [
            'key' => 'besoin',
            'label' => __('Expression de besoin'),
            'status' => $error ? 'error' : ($done ? 'done' : 'current'),
            'reference' => $besoin->reference,
            'url' => route('expression-besoins.besoins.show', $besoin),
            'date' => $besoin->updated_at?->format('d/m/Y'),
            'statut_label' => $st['label'],
        ];
    }

    private static function engagementStep(Engagement $engagement): array
    {
        $st = StatutPresenter::engagement($engagement->statut);
        $value = $engagement->statut instanceof \BackedEnum ? $engagement->statut->value : $engagement->statut;
        $done = in_array($value, ['approuve', 'liquide', 'ordonnance', 'paye', 'cloture'], true);
        $error = in_array($value, ['rejete', 'annule'], true);

        return [
            'key' => 'engagement',
            'label' => __('Engagement'),
            'status' => $error ? 'error' : ($done ? 'done' : 'current'),
            'reference' => $engagement->numero,
            'url' => route('engagements.show', $engagement),
            'date' => $engagement->approved_at?->format('d/m/Y') ?? $engagement->date_engagement?->format('d/m/Y'),
            'statut_label' => $st['label'],
        ];
    }

    private static function liquidationStep(Liquidation $liquidation, ?int $index): array
    {
        $st = StatutPresenter::liquidation($liquidation->statut);
        $value = $liquidation->statut?->value;
        $done = in_array($value, ['certifie', 'transmis_ordonnancement'], true);
        $error = in_array($value, ['rejete', 'annule'], true);

        return [
            'key' => 'liquidation-'.$liquidation->id,
            'label' => $index ? __('Liquidation :n', ['n' => $index]) : __('Liquidation'),
            'status' => $error ? 'error' : ($done ? 'done' : 'current'),
            'reference' => $liquidation->numero,
            'url' => route('liquidations.show', $liquidation),
            'date' => $liquidation->date_liquidation?->format('d/m/Y'),
            'statut_label' => $st['label'],
        ];
    }

    private static function ordonnancementStep(Ordonnancement $ordonnancement, ?int $index): array
    {
        $value = $ordonnancement->statut?->value ?? '';
        $done = in_array($value, ['mandate', 'transmis_paiement'], true);
        $error = in_array($value, ['rejete', 'annule'], true);

        return [
            'key' => 'ordonnancement-'.$ordonnancement->id,
            'label' => $index ? __('Ordonnancement :n', ['n' => $index]) : __('Ordonnancement'),
            'status' => $error ? 'error' : ($done ? 'done' : 'current'),
            'reference' => $ordonnancement->numero,
            'url' => route('ordonnancements.show', $ordonnancement),
            'date' => $ordonnancement->date_ordonnancement?->format('d/m/Y'),
            'statut_label' => $ordonnancement->statut?->libelle() ?? $value,
        ];
    }

    private static function paiementStep(Paiement $paiement, ?int $index): array
    {
        $value = $paiement->statut?->value ?? '';
        $done = in_array($value, ['execute', 'rapproche', 'cloture'], true);
        $error = in_array($value, ['rejete', 'annule', 'echoue'], true);

        return [
            'key' => 'paiement-'.$paiement->id,
            'label' => $index ? __('Paiement :n', ['n' => $index]) : __('Paiement'),
            'status' => $error ? 'error' : ($done ? 'done' : 'current'),
            'reference' => $paiement->numero,
            'url' => route('paiements.show', $paiement),
            'date' => $paiement->date_paiement?->format('d/m/Y'),
            'statut_label' => $paiement->statut?->libelle() ?? $value,
        ];
    }

    /** @return array<string, mixed> */
    private static function pendingStep(string $key, string $label): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'status' => 'pending',
            'reference' => null,
            'url' => null,
            'date' => null,
            'statut_label' => __('En attente'),
        ];
    }
}
