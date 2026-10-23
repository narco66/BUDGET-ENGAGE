<?php

namespace App\Http\Controllers\Ordonnancement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ordonnancement\StoreOrdonnancementDocumentRequest;
use App\Models\Ordonnancement;
use App\Services\Ordonnancement\OrdonnancementGedService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrdonnancementGedController extends Controller
{
    public function __construct(private readonly OrdonnancementGedService $ged)
    {
    }

    public function store(StoreOrdonnancementDocumentRequest $request, Ordonnancement $ordonnancement): RedirectResponse
    {
        $this->authorize('modifier', $ordonnancement);

        $this->ged->deposer(
            $ordonnancement,
            $request->file('fichier'),
            $request->user(),
            $request->input('titre'),
            $request->input('type_piece'),
        );

        return back()->with('status', __('Pièce déposée dans la GED et rattachée à l’ordonnancement.'));
    }

    public function heriter(Request $request, Ordonnancement $ordonnancement): RedirectResponse
    {
        $this->authorize('modifier', $ordonnancement);

        $count = $this->ged->heriterDepuisLiquidation($ordonnancement, $request->user());

        return back()->with('status', __(':n pièce(s) héritée(s) de la liquidation.', ['n' => $count]));
    }
}
