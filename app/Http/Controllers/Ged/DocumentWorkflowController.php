<?php

namespace App\Http\Controllers\Ged;

use App\Http\Controllers\Controller;
use App\Models\GedDocument;
use App\Services\Ged\GedWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DocumentWorkflowController extends Controller
{
    public function __construct(private readonly GedWorkflowService $workflow)
    {
    }

    public function submit(GedDocument $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $this->workflow->start($document, request()->user());

        return redirect()
            ->route('ged.documents.show', $document)
            ->with('success', __('Document soumis au circuit de validation.'));
    }

    public function decide(Request $request, GedDocument $document): RedirectResponse
    {
        $this->authorize('validateDocument', $document);

        $validated = $request->validate([
            'decision' => ['required', 'in:valide,rejete,retourne'],
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->workflow->decide(
            $document,
            $request->user(),
            $validated['decision'],
            $validated['commentaire'] ?? null
        );

        return redirect()
            ->route('ged.documents.show', $document)
            ->with('success', __('Décision enregistrée.'));
    }
}
