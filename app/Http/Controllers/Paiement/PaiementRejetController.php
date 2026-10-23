<?php

namespace App\Http\Controllers\Paiement;

use App\Http\Controllers\Controller;
use App\Http\Requests\Paiement\StoreRejetRequest;
use App\Models\Paiement;
use App\Services\Paiement\PaiementService;
use Illuminate\Http\RedirectResponse;

class PaiementRejetController extends Controller
{
    public function __construct(private readonly PaiementService $service)
    {
    }

    public function store(StoreRejetRequest $request, Paiement $paiement): RedirectResponse
    {
        $this->authorize('signalerRejet', $paiement);

        $this->service->signalerRejet($paiement, $request->validated(), $request->user());

        return redirect()->route('paiements.show', $paiement)
            ->with('status', __('Rejet bancaire enregistré. Le paiement est en échec pour reprise.'));
    }
}
