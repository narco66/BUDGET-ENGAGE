<?php

namespace App\Http\Controllers\Paiement;

use App\Enums\DecisionWorkflow;
use App\Http\Controllers\Controller;
use App\Http\Requests\Paiement\WorkflowDecisionRequest;
use App\Models\Paiement;
use App\Services\Paiement\PaiementWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaiementWorkflowController extends Controller
{
    public function __construct(private readonly PaiementWorkflowService $workflow)
    {
    }

    public function programmer(Request $request, Paiement $paiement): RedirectResponse
    {
        $this->authorize('programmer', $paiement);

        $this->workflow->programmer($paiement, $request->user());

        return redirect()->route('paiements.show', $paiement)
            ->with('status', __('Paiement programmé et soumis au circuit de validation.'));
    }

    public function decision(WorkflowDecisionRequest $request, Paiement $paiement): RedirectResponse
    {
        $this->authorize('decider', $paiement);

        $this->workflow->processDecision(
            $paiement,
            $request->user(),
            DecisionWorkflow::from($request->validated()['decision']),
            $request->input('commentaire'),
        );

        return redirect()->route('paiements.show', $paiement)->with('status', __('Décision enregistrée.'));
    }
}
