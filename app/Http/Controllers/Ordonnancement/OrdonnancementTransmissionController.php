<?php

namespace App\Http\Controllers\Ordonnancement;

use App\Http\Controllers\Controller;
use App\Models\Ordonnancement;
use App\Services\Ordonnancement\OrdonnancementToPaiementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrdonnancementTransmissionController extends Controller
{
    public function __construct(private readonly OrdonnancementToPaiementService $service)
    {
    }

    public function transmit(Request $request, Ordonnancement $ordonnancement): RedirectResponse
    {
        $this->authorize('transmettre', $ordonnancement);

        $paiement = $this->service->transmettre($ordonnancement, $request->user());

        return redirect()->route('ordonnancements.show', $ordonnancement)
            ->with('status', __('Ordonnancement transmis au paiement (n° :id).', ['id' => $paiement->id]));
    }
}
