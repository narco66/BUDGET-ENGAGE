<?php

namespace App\Support\ChaineDepense;

/**
 * Présentation unifiée des statuts — chaîne de la dépense.
 */
final class StatutPresenter
{
    /** @return array{label: string, class: string, icon: string} */
    public static function engagement(mixed $statut): array
    {
        $value = $statut instanceof \BackedEnum ? $statut->value : (string) $statut;

        return match ($value) {
            'brouillon' => ['label' => __('Brouillon'), 'class' => 'badge-brouillon', 'icon' => 'draft'],
            'soumis' => ['label' => __('Soumis'), 'class' => 'badge-soumis', 'icon' => 'send'],
            'en_validation' => ['label' => __('En validation'), 'class' => 'badge-validation', 'icon' => 'clock'],
            'retourne' => ['label' => __('Retourné'), 'class' => 'badge-retourne', 'icon' => 'return'],
            'rejete' => ['label' => __('Rejeté'), 'class' => 'badge-rejete', 'icon' => 'reject'],
            'approuve' => ['label' => __('Approuvé'), 'class' => 'badge-approuve', 'icon' => 'check'],
            'annule' => ['label' => __('Annulé'), 'class' => 'badge-annule', 'icon' => 'cancel'],
            'liquide' => ['label' => __('Liquidé'), 'class' => 'badge-liquide', 'icon' => 'liquidation'],
            'ordonnance' => ['label' => __('Ordonnancé'), 'class' => 'badge-ordonnance', 'icon' => 'order'],
            'paye' => ['label' => __('Payé'), 'class' => 'badge-paye', 'icon' => 'payment'],
            'cloture' => ['label' => __('Clôturé'), 'class' => 'badge-cloture', 'icon' => 'lock'],
            default => ['label' => $value, 'class' => 'badge-neutral', 'icon' => 'neutral'],
        };
    }

    /** @return array{label: string, class: string} */
    public static function besoin(mixed $statut): array
    {
        if ($statut instanceof \App\Enums\StatutBesoin) {
            return ['label' => $statut->libelle(), 'class' => match ($statut) {
                \App\Enums\StatutBesoin::Brouillon => 'badge-brouillon',
                \App\Enums\StatutBesoin::Soumis, \App\Enums\StatutBesoin::EnValidation => 'badge-validation',
                \App\Enums\StatutBesoin::Valide => 'badge-approuve',
                \App\Enums\StatutBesoin::Rejete => 'badge-rejete',
                \App\Enums\StatutBesoin::Annule => 'badge-annule',
                \App\Enums\StatutBesoin::Converti => 'badge-success',
                default => 'badge-neutral',
            }];
        }

        return ['label' => (string) $statut, 'class' => 'badge-neutral'];
    }

    /** @return array{label: string, class: string} */
    public static function liquidation(mixed $statut): array
    {
        $value = $statut instanceof \BackedEnum ? $statut->value : (string) $statut;

        return match ($value) {
            'brouillon' => ['label' => __('Brouillon'), 'class' => 'badge-neutral'],
            'soumis' => ['label' => __('Soumise'), 'class' => 'badge-info'],
            'controle_technique', 'controle_administratif', 'controle_budgetaire', 'controle_financier'
                => ['label' => __('Contrôle'), 'class' => 'badge-info'],
            'valide' => ['label' => __('Validée'), 'class' => 'badge-warning'],
            'certifie' => ['label' => __('Certifiée'), 'class' => 'badge-success'],
            'transmis_ordonnancement' => ['label' => __('Transmise'), 'class' => 'badge-success'],
            'retourne' => ['label' => __('Retournée'), 'class' => 'badge-retourne'],
            'rejete' => ['label' => __('Rejetée'), 'class' => 'badge-rejete'],
            'annule' => ['label' => __('Annulée'), 'class' => 'badge-annule'],
            default => ['label' => $statut instanceof \BackedEnum ? ($statut->libelle() ?? $value) : $value, 'class' => 'badge-neutral'],
        };
    }

    /** @return array{label: string, class: string} */
    public static function ordonnancement(mixed $statut): array
    {
        $value = $statut instanceof \BackedEnum ? $statut->value : (string) $statut;

        return match ($value) {
            'brouillon' => ['label' => __('Brouillon'), 'class' => 'badge-neutral'],
            'soumis' => ['label' => __('Soumis'), 'class' => 'badge-info'],
            'controle_budgetaire' => ['label' => __('Contrôle budgétaire'), 'class' => 'badge-info'],
            'controle_comptable' => ['label' => __('Contrôle comptable'), 'class' => 'badge-info'],
            'controle_financier' => ['label' => __('Contrôle financier'), 'class' => 'badge-info'],
            'visa_ordonnateur' => ['label' => __('Visa ordonnateur'), 'class' => 'badge-info'],
            'valide' => ['label' => __('Validé'), 'class' => 'badge-warning'],
            'mandate' => ['label' => __('Mandaté'), 'class' => 'badge-success'],
            'transmis_paiement' => ['label' => __('Transmis au paiement'), 'class' => 'badge-success'],
            'retourne' => ['label' => __('Retourné'), 'class' => 'badge-retourne'],
            'rejete' => ['label' => __('Rejeté'), 'class' => 'badge-rejete'],
            'annule' => ['label' => __('Annulé'), 'class' => 'badge-annule'],
            default => ['label' => $statut instanceof \BackedEnum ? $statut->libelle() : $value, 'class' => 'badge-neutral'],
        };
    }

    /** @return array{label: string, class: string} */
    public static function paiement(mixed $statut): array
    {
        $value = $statut instanceof \BackedEnum ? $statut->value : (string) $statut;

        return match ($value) {
            'brouillon' => ['label' => __('Brouillon'), 'class' => 'badge-neutral'],
            'programme' => ['label' => __('Programmé'), 'class' => 'badge-info'],
            'controle_tresorerie' => ['label' => __('Contrôle trésorerie'), 'class' => 'badge-info'],
            'controle_comptable' => ['label' => __('Contrôle comptable'), 'class' => 'badge-info'],
            'validation_financiere' => ['label' => __('Validation financière'), 'class' => 'badge-info'],
            'validation_signataires' => ['label' => __('Validation signataires'), 'class' => 'badge-info'],
            'valide' => ['label' => __('Validé'), 'class' => 'badge-warning'],
            'pret_banque' => ['label' => __('Prêt pour banque'), 'class' => 'badge-warning'],
            'transmis_banque' => ['label' => __('Transmis banque'), 'class' => 'badge-info'],
            'execute' => ['label' => __('Exécuté'), 'class' => 'badge-success'],
            'rapproche' => ['label' => __('Rapproché'), 'class' => 'badge-success'],
            'cloture' => ['label' => __('Clôturé'), 'class' => 'badge-cloture'],
            'echoue' => ['label' => __('Échoué'), 'class' => 'badge-rejete'],
            'rejete' => ['label' => __('Rejeté'), 'class' => 'badge-rejete'],
            'retourne' => ['label' => __('Retourné'), 'class' => 'badge-retourne'],
            'annule' => ['label' => __('Annulé'), 'class' => 'badge-annule'],
            default => ['label' => $statut instanceof \BackedEnum ? $statut->libelle() : $value, 'class' => 'badge-neutral'],
        };
    }
}
