<?php

namespace App\Http\Controllers\Execution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Execution\CancelExecutionRequest;
use App\Http\Requests\Execution\StoreOrdonnancementRequest;
use App\Http\Requests\Execution\UpdateOrdonnancementRequest;
use App\Models\Liquidation;
use App\Models\Ordonnancement;
use App\Services\EngagementExecutionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrdonnancementController extends Controller
{
    public function show(Ordonnancement $ordonnancement): View
    {
        $this->authorize('view', $ordonnancement);

        $ordonnancement->load([
            'liquidation.engagement.exerciceBudgetaire',
            'engagementImputation.ligneBudgetaire',
            'createur',
            'paiements.createur',
        ]);

        return view('execution.ordonnancements.show', compact('ordonnancement'));
    }

    public function edit(Ordonnancement $ordonnancement): View
    {
        $this->authorize('update', $ordonnancement);
        $ordonnancement->load('liquidation.engagement');

        return view('execution.ordonnancements.edit', compact('ordonnancement'));
    }

    public function update(UpdateOrdonnancementRequest $request, Ordonnancement $ordonnancement): RedirectResponse
    {
        app(EngagementExecutionService::class)->updateOrdonnancement(
            $ordonnancement,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('execution.ordonnancements.show', $ordonnancement)
            ->with('status', __('execution.messages.ordonnancement_updated'));
    }

    public function cancel(CancelExecutionRequest $request, Ordonnancement $ordonnancement): RedirectResponse
    {
        $this->authorize('cancel', $ordonnancement);

        app(EngagementExecutionService::class)->cancelOrdonnancement(
            $ordonnancement,
            $request->user(),
            $request->validated('motif_annulation')
        );

        return redirect()
            ->route('execution.dashboard', ['tab' => 'ordonnancements'])
            ->with('status', __('execution.messages.ordonnancement_cancelled'));
    }

    public function create(Liquidation $liquidation): View
    {
        $this->authorize('addOrdonnancement', $liquidation);

        $liquidation->load('engagement');

        return view('execution.ordonnancements.create', compact('liquidation'));
    }

    public function store(StoreOrdonnancementRequest $request, Liquidation $liquidation): RedirectResponse
    {
        $ordonnancement = app(EngagementExecutionService::class)->recordOrdonnancement(
            $liquidation,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('execution.ordonnancements.show', $ordonnancement)
            ->with('status', __('execution.messages.ordonnancement_created'));
    }
}
