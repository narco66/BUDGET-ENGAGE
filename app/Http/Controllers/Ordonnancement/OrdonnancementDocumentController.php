<?php

namespace App\Http\Controllers\Ordonnancement;

use App\Http\Controllers\Controller;
use App\Models\Ordonnancement;
use App\Support\DocumentQrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * Génération documentaire PDF d'un ordonnancement : fiche, bordereau, mandat de
 * paiement, ordre de paiement. Le mandat/ordre embarquent le sceau HMAC posé à
 * la génération du mandat.
 */
class OrdonnancementDocumentController extends Controller
{
    public function fiche(Ordonnancement $ordonnancement): Response
    {
        $this->authorize('view', $ordonnancement);

        $ordonnancement->load(['liquidation.engagement.exerciceBudgetaire', 'type', 'fournisseur', 'lignes.ligneBudgetaire', 'lignes.sourceFinancement', 'validations.validateur', 'createur']);

        return Pdf::loadView('ordonnancements.exports.fiche', [
            'ordonnancement' => $ordonnancement,
            'generatedAt' => now(),
        ])->download('fiche-ordonnancement-'.$ordonnancement->numero.'.pdf');
    }

    public function bordereau(Ordonnancement $ordonnancement): Response
    {
        $this->authorize('view', $ordonnancement);

        $ordonnancement->load(['liquidation', 'type', 'fournisseur', 'lignes.ligneBudgetaire']);

        return Pdf::loadView('ordonnancements.exports.bordereau', [
            'ordonnancement' => $ordonnancement,
            'generatedAt' => now(),
        ])->download('bordereau-ordonnancement-'.$ordonnancement->numero.'.pdf');
    }

    public function mandat(Ordonnancement $ordonnancement): Response
    {
        $this->authorize('view', $ordonnancement);

        $ordonnancement->load(['liquidation.engagement', 'fournisseur', 'mandat', 'lignes.ligneBudgetaire']);
        $mandat = $ordonnancement->mandat;

        if ($mandat === null) {
            throw ValidationException::withMessages(['mandat' => __('Aucun mandat généré pour cet ordonnancement.')]);
        }

        $qr = DocumentQrCode::imgTag(DocumentQrCode::payload('MAND', (string) $mandat->numero, $mandat->hash_integrite ?? $ordonnancement->visa_hash));

        return Pdf::loadView('ordonnancements.exports.mandat', [
            'ordonnancement' => $ordonnancement,
            'mandat' => $mandat,
            'qr' => $qr,
            'generatedAt' => now(),
        ])->download('mandat-'.$mandat->numero.'.pdf');
    }

    public function ordrePaiement(Ordonnancement $ordonnancement): Response
    {
        $this->authorize('view', $ordonnancement);

        $ordonnancement->load(['liquidation.engagement', 'fournisseur', 'ordresPaiement']);
        $ordre = $ordonnancement->ordresPaiement->last();

        if ($ordre === null) {
            throw ValidationException::withMessages(['ordre_paiement' => __('Aucun ordre de paiement généré.')]);
        }

        $qr = DocumentQrCode::imgTag(DocumentQrCode::payload('OP', (string) $ordre->numero, $ordonnancement->visa_hash));

        return Pdf::loadView('ordonnancements.exports.ordre_paiement', [
            'ordonnancement' => $ordonnancement,
            'ordre' => $ordre,
            'qr' => $qr,
            'generatedAt' => now(),
        ])->download('ordre-paiement-'.$ordre->numero.'.pdf');
    }
}
