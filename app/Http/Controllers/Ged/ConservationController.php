<?php

namespace App\Http\Controllers\Ged;

use App\Enums\StatutDocumentGed;
use App\Http\Controllers\Controller;
use App\Models\GedDocument;
use App\Services\Ged\GedDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConservationController extends Controller
{
    public function __construct(private readonly GedDocumentService $documents)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', GedDocument::class);

        $fenetreAlerte = now()->addDays(60);

        $aEcheance = GedDocument::query()
            ->visiblePour($request->user())
            ->with('typeDocument')
            ->whereNotNull('date_expiration')
            ->whereDate('date_expiration', '>', now())
            ->whereDate('date_expiration', '<=', $fenetreAlerte)
            ->where('statut', '!=', StatutDocumentGed::Expire->value)
            ->orderBy('date_expiration')
            ->paginate(15, ['*'], 'echeance');

        $expires = GedDocument::query()
            ->visiblePour($request->user())
            ->with('typeDocument')
            ->whereNotNull('date_expiration')
            ->whereDate('date_expiration', '<=', now())
            ->where('statut', '!=', StatutDocumentGed::Expire->value)
            ->orderBy('date_expiration')
            ->paginate(15, ['*'], 'expires');

        return view('ged.conservation.index', compact('aEcheance', 'expires'));
    }

    public function expirer(GedDocument $document): RedirectResponse
    {
        $this->authorize('archive', $document);

        abort_if(
            $document->date_expiration === null || $document->date_expiration->isFuture(),
            422,
            __("Ce document n'est pas arrivé à échéance.")
        );

        $document->update([
            'statut' => StatutDocumentGed::Expire->value,
            'updated_by' => request()->user()->id,
        ]);

        $this->documents->journal($document, request()->user(), 'conservation_expiration');

        return redirect()
            ->route('ged.conservation.index')
            ->with('success', __('Document marqué comme expiré.'));
    }
}
