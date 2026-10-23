<?php

namespace App\Http\Controllers\Ordonnancement;

use App\Enums\DecisionWorkflow;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ordonnancement\WorkflowDecisionRequest;
use App\Models\Ordonnancement;
use App\Services\Ordonnancement\OrdonnancementWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrdonnancementWorkflowController extends Controller
{
    public function __construct(private readonly OrdonnancementWorkflowService $workflow)
    {
    }

    public function submit(Request $request, Ordonnancement $ordonnancement): RedirectResponse
    {
        $this->authorize('soumettre', $ordonnancement);

        $this->workflow->soumettre($ordonnancement, $request->user());

        return redirect()->route('ordonnancements.show', $ordonnancement)
            ->with('status', __('Ordonnancement soumis au circuit de validation.'));
    }

    public function decision(WorkflowDecisionRequest $request, Ordonnancement $ordonnancement): RedirectResponse
    {
        $this->authorize('decider', $ordonnancement);

        $this->workflow->processDecision(
            $ordonnancement,
            $request->user(),
            DecisionWorkflow::from($request->validated()['decision']),
            $request->input('commentaire'),
        );

        return redirect()->route('ordonnancements.show', $ordonnancement)
            ->with('status', __('Décision enregistrée.'));
    }
}
