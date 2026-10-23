<?php

namespace App\Http\Controllers\Liquidation;

use App\Http\Controllers\Controller;
use App\Models\Liquidation;
use App\Services\Liquidation\LiquidationToOrdonnancementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LiquidationTransmissionController extends Controller
{
    public function __construct(private readonly LiquidationToOrdonnancementService $service)
    {
    }

    public function transmit(Request $request, Liquidation $liquidation): RedirectResponse
    {
        $this->authorize('transmettre', $liquidation);

        $ordonnancement = $this->service->transmettre($liquidation, $request->user());

        return redirect()->route('liquidations.show', $liquidation)
            ->with('status', __('Liquidation transmise à l’ordonnancement (n° :id).', ['id' => $ordonnancement->id]));
    }
}
