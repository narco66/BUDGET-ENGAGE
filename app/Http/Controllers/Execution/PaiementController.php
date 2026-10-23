<?php

namespace App\Http\Controllers\Execution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Execution\CancelExecutionRequest;
use App\Http\Requests\Execution\StorePaiementRequest;
use App\Http\Requests\Execution\UpdatePaiementRequest;
use App\Models\Ordonnancement;
use App\Models\Paiement;
use App\Services\EngagementExecutionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaiementController extends Controller
{
    public function show(Paiement $paiement): View
    {
        $this->authorize('view', $paiement);

        $paiement->load([
            'ordonnancement.liquidation.engagement.exerciceBudgetaire',
            'engagementImputation.ligneBudgetaire',
            'createur',
        ]);

        return view('execution.paiements.show', compact('paiement'));
    }

    public function edit(Paiement $paiement): View
    {
        $this->authorize('update', $paiement);
        $paiement->load('ordonnancement.liquidation.engagement');

        return view('execution.paiements.edit', compact('paiement'));
    }

    public function update(UpdatePaiementRequest $request, Paiement $paiement): RedirectResponse
    {
        app(EngagementExecutionService::class)->updatePaiement(
            $paiement,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('execution.paiements.show', $paiement)
            ->with('status', __('execution.messages.paiement_updated'));
    }

    public function cancel(CancelExecutionRequest $request, Paiement $paiement): RedirectResponse
    {
        $this->authorize('cancel', $paiement);

        app(EngagementExecutionService::class)->cancelPaiement(
            $paiement,
            $request->user(),
            $request->validated('motif_annulation')
        );

        return redirect()
            ->route('execution.dashboard', ['tab' => 'paiements'])
            ->with('status', __('execution.messages.paiement_cancelled'));
    }

    public function create(Ordonnancement $ordonnancement): View
    {
        $this->authorize('addPaiement', $ordonnancement);

        $ordonnancement->load('liquidation.engagement');

        return view('execution.paiements.create', compact('ordonnancement'));
    }

    public function store(StorePaiementRequest $request, Ordonnancement $ordonnancement): RedirectResponse
    {
        $paiement = app(EngagementExecutionService::class)->recordPaiement(
            $ordonnancement,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('execution.paiements.show', $paiement)
            ->with('status', __('execution.messages.paiement_created'));
    }
}
