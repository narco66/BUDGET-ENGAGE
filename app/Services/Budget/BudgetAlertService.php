<?php

namespace App\Services\Budget;

use App\Models\AlerteBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\ParametreControle;
use App\Models\User;
use App\Notifications\BudgetThresholdNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Alertes de seuil sur les credits : detecte les lignes budgetaires quasi epuisees ou depassees.
 */
class BudgetAlertService
{
    private const RANG = ['avertissement' => 1, 'critique' => 2, 'depassement' => 3];

    /**
     * Evalue une ligne et met a jour son alerte (ou la resout si le seuil n'est plus atteint).
     */
    public function evaluate(LigneBudgetaire $ligne, bool $notify = true): ?AlerteBudgetaire
    {
        $plafond = (float) $ligne->montant_revise > 0 ? (float) $ligne->montant_revise : (float) $ligne->montant_vote;
        if ($plafond <= 0) {
            AlerteBudgetaire::query()->where('ligne_budgetaire_id', $ligne->id)->delete();

            return null;
        }

        $engage = (float) $ligne->montant_engage;
        $taux = round($engage / $plafond * 100, 2);
        [$seuilAlerte, $seuilCritique] = $this->seuils();

        $niveau = null;
        $seuil = 0;
        if ($engage > $plafond) {
            $niveau = 'depassement';
            $seuil = 100;
        } elseif ($taux >= $seuilCritique) {
            $niveau = 'critique';
            $seuil = $seuilCritique;
        } elseif ($taux >= $seuilAlerte) {
            $niveau = 'avertissement';
            $seuil = $seuilAlerte;
        }

        $existing = AlerteBudgetaire::query()->where('ligne_budgetaire_id', $ligne->id)->first();

        // En dessous du seuil : on resout l'alerte (conservee dans l'historique), sans suppression.
        if ($niveau === null) {
            if ($existing !== null && $existing->statut !== 'resolue') {
                $existing->update([
                    'statut' => 'resolue',
                    'taux' => $taux,
                    'montant_engage' => $engage,
                    'plafond' => $plafond,
                    'message' => __('Crédit revenu sous le seuil sur la ligne :code (:taux% engagé).', ['code' => $ligne->code, 'taux' => $taux]),
                ]);
            }

            return null;
        }

        // Reouverture si nouvelle, anterieurement resolue, ou escalade de gravite.
        $escalated = $existing === null
            || $existing->statut === 'resolue'
            || self::RANG[$niveau] > (self::RANG[$existing->niveau] ?? 0);

        $message = $this->message($ligne, $niveau, $taux);

        $alerte = AlerteBudgetaire::query()->updateOrCreate(
            ['ligne_budgetaire_id' => $ligne->id],
            array_merge([
                'exercice_budgetaire_id' => $ligne->exercice_budgetaire_id,
                'code' => $ligne->code,
                'type' => 'seuil_engagement',
                'niveau' => $niveau,
                'seuil' => $seuil,
                'taux' => $taux,
                'montant_engage' => $engage,
                'plafond' => $plafond,
                'message' => $message,
            ], $escalated ? ['statut' => 'nouvelle', 'detectee_le' => now(), 'traitee_par' => null, 'traitee_le' => null, 'commentaire_traitement' => null] : [])
        );

        if ($notify && $escalated && in_array($niveau, ['critique', 'depassement'], true)) {
            $this->notify($alerte);
        }

        return $alerte;
    }

    /**
     * Marque une alerte comme traitee (accusee de reception), avec tracabilite.
     */
    public function traiter(AlerteBudgetaire $alerte, User $user, ?string $commentaire = null): AlerteBudgetaire
    {
        $alerte->update([
            'statut' => 'traitee',
            'traitee_par' => $user->id,
            'traitee_le' => now(),
            'commentaire_traitement' => $commentaire,
        ]);

        return $alerte;
    }

    /**
     * Restaure (rouvre) une alerte traitee ou resolue.
     */
    public function restaurer(AlerteBudgetaire $alerte, User $user): AlerteBudgetaire
    {
        $alerte->update([
            'statut' => 'nouvelle',
            'traitee_par' => null,
            'traitee_le' => null,
            'commentaire_traitement' => null,
            'detectee_le' => now(),
        ]);

        return $alerte;
    }

    /**
     * @return array{0: int, 1: int} [seuil_alerte, seuil_critique]
     */
    public function seuils(): array
    {
        $alerte = (int) ($this->param('seuil_consommation_budget_alerte') ?? 80);
        $critique = (int) ($this->param('seuil_consommation_budget_critique') ?? 95);

        return [$alerte, max($critique, $alerte)];
    }

    private function param(string $cle): ?string
    {
        $p = ParametreControle::query()->where('cle', $cle)->where('actif', true)->first();

        return $p?->valeur;
    }

    private function message(LigneBudgetaire $ligne, string $niveau, float $taux): string
    {
        return match ($niveau) {
            'depassement' => __('Dépassement de crédit sur la ligne :code (:taux% engagé).', ['code' => $ligne->code, 'taux' => $taux]),
            'critique' => __('Crédit critique sur la ligne :code : :taux% engagé.', ['code' => $ligne->code, 'taux' => $taux]),
            default => __('Crédit en alerte sur la ligne :code : :taux% engagé.', ['code' => $ligne->code, 'taux' => $taux]),
        };
    }

    private function notify(AlerteBudgetaire $alerte): void
    {
        $destinataires = User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['administrateur_general', 'administrateur_budget', 'controleur_budgetaire']))
            ->get();

        if ($destinataires->isNotEmpty()) {
            Notification::send($destinataires, new BudgetThresholdNotification($alerte));
        }
    }
}
