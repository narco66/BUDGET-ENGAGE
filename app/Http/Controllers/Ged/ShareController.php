<?php

namespace App\Http\Controllers\Ged;

use App\Http\Controllers\Controller;
use App\Models\GedDocument;
use App\Models\GedPartage;
use App\Services\Ged\GedDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShareController extends Controller
{
    public function __construct(private readonly GedDocumentService $documents)
    {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('ged.share') || $request->user()?->can('ged.view'), 403);

        $partages = GedPartage::query()
            ->where('est_actif', true)
            ->where(function ($q) use ($request): void {
                $q->whereHas('document', fn ($d) => $d->visiblePour($request->user()))
                    ->orWhere('partage_par', $request->user()->id);
            })
            ->with(['document', 'partagePar', 'partageAvec'])
            ->latest()
            ->paginate(20);

        return view('ged.partages.index', compact('partages'));
    }

    public function store(Request $request, GedDocument $document): RedirectResponse
    {
        $this->authorize('share', $document);

        $validated = $request->validate([
            'partage_avec_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'partage_avec_role' => ['nullable', 'string', 'max:100'],
            'niveau_acces' => ['required', 'in:lecture,telechargement,edition'],
            'date_expiration' => ['nullable', 'date', 'after:today'],
        ]);

        $document->partages()->create([
            'partage_par' => $request->user()->id,
            'partage_avec_user_id' => $validated['partage_avec_user_id'] ?? null,
            'partage_avec_role' => $validated['partage_avec_role'] ?? null,
            'niveau_acces' => $validated['niveau_acces'],
            'token' => Str::random(48),
            'date_expiration' => $validated['date_expiration'] ?? null,
            'est_actif' => true,
        ]);

        $this->documents->journal($document, $request->user(), 'partage');

        return redirect()
            ->route('ged.documents.show', $document)
            ->with('success', __('Document partagé.'));
    }

    public function destroy(GedPartage $partage): RedirectResponse
    {
        $partage->loadMissing('document');
        $this->authorize('share', $partage->document);

        abort_unless(
            $partage->partage_par === request()->user()->id || request()->user()->can('ged.admin'),
            403
        );

        $partage->update(['est_actif' => false]);

        return redirect()->back()->with('success', __('Partage révoqué.'));
    }
}
