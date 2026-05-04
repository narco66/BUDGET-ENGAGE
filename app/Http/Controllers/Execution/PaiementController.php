<?php

namespace App\Http\Controllers\Execution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Execution\StorePaiementRequest;
use App\Models\Ordonnancement;
use App\Services\EngagementExecutionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaiementController extends Controller
{
    public function create(Ordonnancement $ordonnancement): View
    {
        $this->authorize('addPaiement', $ordonnancement);

        $ordonnancement->load('liquidation.engagement');

        return view('execution.paiements.create', compact('ordonnancement'));
    }

    public function store(StorePaiementRequest $request, Ordonnancement $ordonnancement): RedirectResponse
    {
        app(EngagementExecutionService::class)->recordPaiement(
            $ordonnancement,
            $request->validated(),
            $request->user()
        );

        $ordonnancement->load('liquidation.engagement');

        return redirect()
            ->route('engagements.show', $ordonnancement->liquidation->engagement)
            ->with('status', __('execution.messages.paiement_created'));
    }
}
