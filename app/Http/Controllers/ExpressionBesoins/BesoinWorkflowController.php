<?php

namespace App\Http\Controllers\ExpressionBesoins;

use App\Http\Controllers\Controller;
use App\Models\ExpressionBesoin;
use App\Services\ExpressionBesoin\ExpressionBesoinWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BesoinWorkflowController extends Controller
{
    public function __construct(private readonly ExpressionBesoinWorkflowService $workflow)
    {
    }

    public function submit(ExpressionBesoin $expression_besoin): RedirectResponse
    {
        $this->authorize('submit', $expression_besoin);

        $this->workflow->submit($expression_besoin, request()->user());

        return redirect()
            ->route('expression-besoins.besoins.show', $expression_besoin)
            ->with('success', __('Besoin soumis au circuit de validation.'));
    }

    public function decide(Request $request, ExpressionBesoin $expression_besoin): RedirectResponse
    {
        $this->authorize('validateBesoin', $expression_besoin);

        $validated = $request->validate([
            'decision' => ['required', 'in:valide,rejete,retourne'],
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->workflow->decide(
            $expression_besoin,
            $request->user(),
            $validated['decision'],
            $validated['commentaire'] ?? null
        );

        return redirect()
            ->route('expression-besoins.besoins.show', $expression_besoin)
            ->with('success', __('Décision enregistrée.'));
    }

    public function cancel(Request $request, ExpressionBesoin $expression_besoin): RedirectResponse
    {
        $this->authorize('cancel', $expression_besoin);

        $validated = $request->validate([
            'motif' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->workflow->cancel($expression_besoin, $request->user(), $validated['motif'] ?? null);

        return redirect()
            ->route('expression-besoins.besoins.show', $expression_besoin)
            ->with('success', __('Besoin annulé.'));
    }
}
