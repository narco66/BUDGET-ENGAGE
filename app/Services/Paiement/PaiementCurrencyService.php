<?php

namespace App\Services\Paiement;

use App\Models\Paiement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Moteur multi-devises : conversion devise d'origine → devise de paiement,
 * calcul et historisation de l'écart de change (gain/perte).
 */
class PaiementCurrencyService
{
    private const SCALE = 2;

    /**
     * Convertit un montant selon un taux de change.
     */
    public function convertir(string $montant, string $taux): string
    {
        return bcmul($montant, $taux, self::SCALE);
    }

    /**
     * Applique le change au paiement : calcule l'écart de change si la devise de
     * paiement diffère de la devise d'origine, et historise l'opération.
     */
    public function appliquer(Paiement $paiement, ?User $user = null): Paiement
    {
        $devise = $paiement->devise ?? 'XAF';
        $devisePaiement = $paiement->devise_paiement ?? $devise;
        $taux = (string) ($paiement->taux_change ?? '1');

        if ($devise === $devisePaiement || bccomp($taux, '0', 6) <= 0) {
            $paiement->update(['ecart_change' => 0]);

            return $paiement;
        }

        $base = (string) ($paiement->montant_ordonnance_ref ?? '0');
        $converti = $this->convertir($base, $taux);
        $ecart = bcsub($converti, $base, self::SCALE);

        $paiement->update(['ecart_change' => $ecart]);

        return DB::transaction(function () use ($paiement, $base, $taux, $converti, $ecart, $user): Paiement {
            $paiement->calculs()->create([
                'type_calcul' => 'ecart_change',
                'formule' => 'Écart = (montant × taux) − montant',
                'entrees' => ['montant' => $base, 'taux' => $taux, 'converti' => $converti],
                'resultat' => $ecart,
                'created_by' => $user?->id,
            ]);

            return $paiement;
        });
    }
}
