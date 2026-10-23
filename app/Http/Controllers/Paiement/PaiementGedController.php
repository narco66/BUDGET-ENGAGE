<?php

namespace App\Http\Controllers\Paiement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Paiement\StorePaiementDocumentRequest;
use App\Models\Paiement;
use App\Services\Paiement\PaiementGedService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaiementGedController extends Controller
{
    public function __construct(private readonly PaiementGedService $ged)
    {
    }

    public function store(StorePaiementDocumentRequest $request, Paiement $paiement): RedirectResponse
    {
        $this->authorize('modifier', $paiement);

        $this->ged->deposer(
            $paiement,
            $request->file('fichier'),
            $request->user(),
            $request->input('titre'),
            $request->input('type_piece'),
        );

        return back()->with('status', __('Pièce déposée dans la GED et rattachée au paiement.'));
    }

    public function heriter(Request $request, Paiement $paiement): RedirectResponse
    {
        $this->authorize('modifier', $paiement);

        $count = $this->ged->heriterDepuisOrdonnancement($paiement, $request->user());

        return back()->with('status', __(':n pièce(s) héritée(s) de l’ordonnancement.', ['n' => $count]));
    }
}
