<?php

namespace App\Http\Controllers\Liquidation;

use App\Http\Controllers\Controller;
use App\Models\Liquidation;
use App\Support\DocumentQrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

/**
 * Génération documentaire PDF d'une liquidation : certificat, fiche détaillée,
 * bordereau de transmission. Le certificat embarque le sceau cryptographique
 * (numéro + empreinte HMAC) posé à la certification.
 */
class LiquidationDocumentController extends Controller
{
    public function certificat(Liquidation $liquidation): Response
    {
        $this->authorize('view', $liquidation);

        $liquidation->load(['engagement.exerciceBudgetaire', 'type', 'fournisseur', 'lignes.ligneBudgetaire', 'lignes.sourceFinancement', 'validateur']);

        $qr = $liquidation->certificat_numero
            ? DocumentQrCode::imgTag(DocumentQrCode::payload('LIQ', (string) $liquidation->certificat_numero, $liquidation->certificat_hash))
            : null;

        return Pdf::loadView('liquidations.exports.certificat', [
            'liquidation' => $liquidation,
            'qr' => $qr,
            'generatedAt' => now(),
        ])->download('certificat-liquidation-'.$liquidation->numero.'.pdf');
    }

    public function fiche(Liquidation $liquidation): Response
    {
        $this->authorize('view', $liquidation);

        $liquidation->load(['engagement.exerciceBudgetaire', 'type', 'fournisseur', 'lignes.ligneBudgetaire', 'lignes.sourceFinancement', 'lignes.activite', 'serviceFait', 'receptions', 'validations.validateur', 'createur']);

        return Pdf::loadView('liquidations.exports.fiche', [
            'liquidation' => $liquidation,
            'generatedAt' => now(),
        ])->download('fiche-liquidation-'.$liquidation->numero.'.pdf');
    }

    public function bordereau(Liquidation $liquidation): Response
    {
        $this->authorize('view', $liquidation);

        $liquidation->load(['engagement', 'type', 'fournisseur', 'lignes.ligneBudgetaire']);

        return Pdf::loadView('liquidations.exports.bordereau', [
            'liquidation' => $liquidation,
            'generatedAt' => now(),
        ])->download('bordereau-liquidation-'.$liquidation->numero.'.pdf');
    }
}
