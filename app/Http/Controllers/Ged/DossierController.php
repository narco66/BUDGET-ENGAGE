<?php

namespace App\Http\Controllers\Ged;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ged\StoreGedDossierRequest;
use App\Http\Requests\Ged\UpdateGedDossierRequest;
use App\Models\ExerciceBudgetaire;
use App\Models\GedCategorie;
use App\Models\GedDossier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DossierController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(GedDossier::class, 'dossier');
    }

    public function index(): View
    {
        $dossiers = GedDossier::query()
            ->with(['parent', 'categorie', 'exerciceBudgetaire'])
            ->withCount('documents')
            ->orderBy('libelle')
            ->paginate(20);

        return view('ged.dossiers.index', compact('dossiers'));
    }

    public function create(): View
    {
        return view('ged.dossiers.create', $this->options());
    }

    public function store(StoreGedDossierRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        GedDossier::query()->create($data);

        return redirect()->route('ged.dossiers.index')->with('success', __('Dossier créé.'));
    }

    public function edit(GedDossier $dossier): View
    {
        return view('ged.dossiers.edit', array_merge(['dossier' => $dossier], $this->options($dossier)));
    }

    public function update(UpdateGedDossierRequest $request, GedDossier $dossier): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $dossier->update($data);

        return redirect()->route('ged.dossiers.index')->with('success', __('Dossier mis à jour.'));
    }

    public function destroy(GedDossier $dossier): RedirectResponse
    {
        $dossier->delete();

        return redirect()->route('ged.dossiers.index')->with('success', __('Dossier archivé.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function options(?GedDossier $exclude = null): array
    {
        $parents = GedDossier::query()
            ->when($exclude, fn ($q) => $q->whereKeyNot($exclude->id))
            ->orderBy('libelle')
            ->get();

        return [
            'parents' => $parents,
            'categories' => GedCategorie::query()->where('statut', 'actif')->orderBy('libelle')->get(),
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
        ];
    }
}
