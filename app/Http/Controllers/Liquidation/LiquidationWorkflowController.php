<?php

namespace App\Http\Controllers\Liquidation;

use App\Enums\DecisionWorkflow;
use App\Http\Controllers\Controller;
use App\Http\Requests\Liquidation\WorkflowDecisionRequest;
use App\Models\Liquidation;
use App\Services\Liquidation\LiquidationWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LiquidationWorkflowController extends Controller
{
    public function __construct(private readonly LiquidationWorkflowService $workflow)
    {
    }

    public function submit(Request $request, Liquidation $liquidation): RedirectResponse
    {
        $this->authorize('soumettre', $liquidation);

        $this->workflow->soumettre($liquidation, $request->user());

        return redirect()->route('liquidations.show', $liquidation)
            ->with('status', __('Liquidation soumise au circuit de validation.'));
    }

    public function decision(WorkflowDecisionRequest $request, Liquidation $liquidation): RedirectResponse
    {
        $this->authorize('decider', $liquidation);

        $this->workflow->processDecision(
            $liquidation,
            $request->user(),
            DecisionWorkflow::from($request->validated()['decision']),
            $request->input('commentaire'),
        );

        return redirect()->route('liquidations.show', $liquidation)
            ->with('status', __('Décision enregistrée.'));
    }
}
