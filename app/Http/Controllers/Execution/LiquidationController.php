<?php

namespace App\Http\Controllers\Execution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Execution\CancelExecutionRequest;
use App\Http\Requests\Execution\StoreLiquidationRequest;
use App\Http\Requests\Execution\UpdateLiquidationRequest;
use App\Models\Engagement;
use App\Models\Liquidation;
use App\Services\EngagementExecutionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LiquidationController extends Controller
{
    public function show(Liquidation $liquidation): View
    {
        $this->authorize('view', $liquidation);

        $liquidation->load([
            'engagement.exerciceBudgetaire',
            'engagementImputation.ligneBudgetaire',
            'createur',
            'ordonnancements.createur',
        ]);

        return view('execution.liquidations.show', compact('liquidation'));
    }

    public function edit(Liquidation $liquidation): View
    {
        $this->authorize('update', $liquidation);
        $liquidation->load('engagement');

        return view('execution.liquidations.edit', compact('liquidation'));
    }

    public function update(UpdateLiquidationRequest $request, Liquidation $liquidation): RedirectResponse
    {
        app(EngagementExecutionService::class)->updateLiquidation(
            $liquidation,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('execution.liquidations.show', $liquidation)
            ->with('status', __('execution.messages.liquidation_updated'));
    }

    public function cancel(CancelExecutionRequest $request, Liquidation $liquidation): RedirectResponse
    {
        $this->authorize('cancel', $liquidation);

        $engagement = $liquidation->engagement;

        app(EngagementExecutionService::class)->cancelLiquidation(
            $liquidation,
            $request->user(),
            $request->validated('motif_annulation')
        );

        return redirect()
            ->route('execution.dashboard', ['tab' => 'liquidations'])
            ->with('status', __('execution.messages.liquidation_cancelled'));
    }

    public function create(Engagement $engagement): View
    {
        $this->authorize('createLiquidation', $engagement);
        $engagement->load('imputations.ligneBudgetaire');

        return view('execution.liquidations.create', compact('engagement'));
    }

    public function store(StoreLiquidationRequest $request, Engagement $engagement): RedirectResponse
    {
        $liquidation = app(EngagementExecutionService::class)->recordLiquidation(
            $engagement,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('execution.liquidations.show', $liquidation)
            ->with('status', __('execution.messages.liquidation_created'));
    }
}
