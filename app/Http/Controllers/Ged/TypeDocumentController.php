<?php

namespace App\Http\Controllers\Ged;

use App\Enums\NiveauConfidentialiteGed;
use App\Enums\SortFinalConservationGed;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ged\StoreGedTypeDocumentRequest;
use App\Http\Requests\Ged\UpdateGedTypeDocumentRequest;
use App\Models\GedCategorie;
use App\Models\GedTypeDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TypeDocumentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(GedTypeDocument::class, 'type_document');
    }

    public function index(): View
    {
        $types = GedTypeDocument::query()
            ->with('categorie')
            ->withCount('documents')
            ->orderBy('ordre')
            ->orderBy('libelle')
            ->paginate(20);

        return view('ged.types.index', compact('types'));
    }

    public function create(): View
    {
        return view('ged.types.create', $this->options());
    }

    public function store(StoreGedTypeDocumentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        GedTypeDocument::query()->create($data);

        return redirect()->route('ged.types-documents.index')->with('success', __('Type de document créé.'));
    }

    public function edit(GedTypeDocument $typeDocument): View
    {
        return view('ged.types.edit', array_merge(['typeDocument' => $typeDocument], $this->options()));
    }

    public function update(UpdateGedTypeDocumentRequest $request, GedTypeDocument $typeDocument): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $typeDocument->update($data);

        return redirect()->route('ged.types-documents.index')->with('success', __('Type de document mis à jour.'));
    }

    public function destroy(GedTypeDocument $typeDocument): RedirectResponse
    {
        $typeDocument->delete();

        return redirect()->route('ged.types-documents.index')->with('success', __('Type de document archivé.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function options(): array
    {
        return [
            'categories' => GedCategorie::query()->where('statut', 'actif')->orderBy('libelle')->get(),
            'niveaux' => NiveauConfidentialiteGed::cases(),
            'sortsFinaux' => SortFinalConservationGed::cases(),
        ];
    }
}
