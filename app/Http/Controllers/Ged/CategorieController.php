<?php

namespace App\Http\Controllers\Ged;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ged\StoreGedCategorieRequest;
use App\Http\Requests\Ged\UpdateGedCategorieRequest;
use App\Models\GedCategorie;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategorieController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(GedCategorie::class, 'categorie');
    }

    public function index(): View
    {
        $categories = GedCategorie::query()
            ->with('parent')
            ->withCount(['typesDocuments', 'documents'])
            ->orderBy('ordre')
            ->orderBy('libelle')
            ->paginate(20);

        return view('ged.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $parents = GedCategorie::query()->orderBy('libelle')->get();

        return view('ged.categories.create', compact('parents'));
    }

    public function store(StoreGedCategorieRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        GedCategorie::query()->create($data);

        return redirect()->route('ged.categories.index')->with('success', __('Catégorie créée.'));
    }

    public function edit(GedCategorie $categorie): View
    {
        $parents = GedCategorie::query()->whereKeyNot($categorie->id)->orderBy('libelle')->get();

        return view('ged.categories.edit', compact('categorie', 'parents'));
    }

    public function update(UpdateGedCategorieRequest $request, GedCategorie $categorie): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $categorie->update($data);

        return redirect()->route('ged.categories.index')->with('success', __('Catégorie mise à jour.'));
    }

    public function destroy(GedCategorie $categorie): RedirectResponse
    {
        $categorie->delete();

        return redirect()->route('ged.categories.index')->with('success', __('Catégorie archivée.'));
    }
}
