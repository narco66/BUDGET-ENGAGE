<?php

namespace App\Http\Controllers\Rbm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rbm\Concerns\AppliesRbmListingFilters;
use App\Http\Requests\Rbm\StoreSousProduitRequest;
use App\Http\Requests\Rbm\UpdateSousProduitRequest;
use App\Models\SousProduit;
use App\Models\Produit;
use App\Services\Rbm\RbmReferentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SousProduitController extends Controller
{
    use AppliesRbmListingFilters;

    public function __construct(protected RbmReferentialService $rbmService)
    {
        $this->authorizeResource(SousProduit::class, 'sous_produit');
    }

    public function index(Request $request): View
    {
        $query = SousProduit::query()->with('produit')->withCount('activites');
        if ($request->filled('produit_id')) {
            $query->where('produit_id', $request->integer('produit_id'));
        }
        $sous_produits = $this->applyListingFilters($query, $request)->paginate(15)->withQueryString();
        $produits = Produit::query()->orderBy('libelle')->get();

        return view('rbm.sous-produits.index', compact('sous_produits', 'produits'));
    }

    public function create(): View
    {
        $produits = Produit::query()->orderBy('libelle')->get();

        return view('rbm.sous-produits.create', compact('produits'));
    }

    public function store(StoreSousProduitRequest $request): RedirectResponse
    {
        $this->rbmService->create(SousProduit::class, $request->validated(), $request->user());

        return redirect()->route('rbm.sous-produits.index')->with('status', __('Enregistrement créé.'));
    }

    public function show(SousProduit $sous_produit): View
    {
        $sous_produit->load('produit')->loadCount('activites');

        return view('rbm.sous-produits.show', compact('sous_produit'));
    }

    public function edit(SousProduit $sous_produit): View
    {
        $produits = Produit::query()->orderBy('libelle')->get();

        return view('rbm.sous-produits.edit', compact('sous_produit', 'produits'));
    }

    public function update(UpdateSousProduitRequest $request, SousProduit $sous_produit): RedirectResponse
    {
        $this->rbmService->update($sous_produit, $request->validated(), $request->user());

        return redirect()->route('rbm.sous-produits.index')->with('status', __('Enregistrement mis à jour.'));
    }

    public function destroy(SousProduit $sous_produit): RedirectResponse
    {
        $this->rbmService->archive($sous_produit, request()->user());

        return redirect()->route('rbm.sous-produits.index')->with('status', __('Enregistrement archivé.'));
    }
}
