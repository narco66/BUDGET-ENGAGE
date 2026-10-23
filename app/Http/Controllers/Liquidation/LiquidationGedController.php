<?php

namespace App\Http\Controllers\Liquidation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Liquidation\StoreLiquidationDocumentRequest;
use App\Models\Liquidation;
use App\Services\Liquidation\LiquidationGedService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LiquidationGedController extends Controller
{
    public function __construct(private readonly LiquidationGedService $ged)
    {
    }

    public function store(StoreLiquidationDocumentRequest $request, Liquidation $liquidation): RedirectResponse
    {
        $this->authorize('modifier', $liquidation);

        $this->ged->deposer(
            $liquidation,
            $request->file('fichier'),
            $request->user(),
            $request->input('titre'),
            $request->input('type_piece'),
        );

        return back()->with('status', __('Pièce déposée dans la GED et rattachée à la liquidation.'));
    }

    public function heriter(Request $request, Liquidation $liquidation): RedirectResponse
    {
        $this->authorize('modifier', $liquidation);

        $count = $this->ged->heriterDepuisEngagement($liquidation, $request->user());

        return back()->with('status', __(':n pièce(s) héritée(s) de l’engagement.', ['n' => $count]));
    }
}
