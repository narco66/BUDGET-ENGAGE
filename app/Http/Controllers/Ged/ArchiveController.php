<?php

namespace App\Http\Controllers\Ged;

use App\Http\Controllers\Controller;
use App\Models\GedDocument;
use App\Services\Ged\GedArchiveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArchiveController extends Controller
{
    public function __construct(private readonly GedArchiveService $archives)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', GedDocument::class);

        $query = GedDocument::query()
            ->visiblePour($request->user())
            ->archives()
            ->with(['typeDocument', 'archiveur'])
            ->orderByDesc('date_archivage');

        if ($request->filled('annee')) {
            $query->whereYear('date_archivage', $request->integer('annee'));
        }

        if ($request->filled('module')) {
            $query->where('module_source', $request->input('module'));
        }

        $documents = $query->paginate(20)->appends($request->query());

        return view('ged.archives.index', compact('documents'));
    }

    public function archive(Request $request, GedDocument $document): RedirectResponse
    {
        $this->authorize('archive', $document);

        $validated = $request->validate([
            'motif' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->archives->archive($document, $request->user(), $validated['motif'] ?? null);

        return redirect()
            ->route('ged.documents.show', $document)
            ->with('success', __('Document archivé et verrouillé en lecture seule.'));
    }

    public function restore(GedDocument $document): RedirectResponse
    {
        $this->authorize('restore', $document);

        $this->archives->restore($document, request()->user());

        return redirect()
            ->route('ged.documents.show', $document)
            ->with('success', __('Document restauré depuis les archives.'));
    }
}
