<?php

namespace App\Http\Controllers\Paiement;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Support\DocumentQrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

/**
 * Génération documentaire PDF d'un paiement : ordre de virement, bordereau
 * bancaire, avis de paiement, reçu, quittance. Les pièces de décaissement
 * embarquent un QR code et le sceau HMAC posé à l'exécution.
 */
class PaiementDocumentController extends Controller
{
    public function ordreVirement(Paiement $paiement): Response
    {
        return $this->render($paiement, 'ordre_virement', 'OV', 'ordre-virement');
    }

    public function bordereau(Paiement $paiement): Response
    {
        return $this->render($paiement, 'bordereau', 'BORD', 'bordereau-bancaire');
    }

    public function avis(Paiement $paiement): Response
    {
        return $this->render($paiement, 'avis', 'AVIS', 'avis-paiement');
    }

    public function recu(Paiement $paiement): Response
    {
        return $this->render($paiement, 'recu', 'RECU', 'recu');
    }

    public function quittance(Paiement $paiement): Response
    {
        return $this->render($paiement, 'quittance', 'QUIT', 'quittance');
    }

    private function render(Paiement $paiement, string $vue, string $typeQr, string $slug): Response
    {
        $this->authorize('view', $paiement);

        $paiement->load(['ordonnancement.liquidation.engagement', 'type', 'banque', 'fournisseur', 'lignes.ligneBudgetaire']);

        $qr = $paiement->preuve_numero
            ? DocumentQrCode::imgTag(DocumentQrCode::payload($typeQr, (string) ($paiement->preuve_numero ?? $paiement->numero), $paiement->preuve_hash))
            : null;

        return Pdf::loadView('paiements.exports.'.$vue, [
            'paiement' => $paiement,
            'qr' => $qr,
            'generatedAt' => now(),
        ])->download($slug.'-'.$paiement->numero.'.pdf');
    }
}
