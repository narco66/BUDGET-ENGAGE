<?php

namespace App\Http\Controllers\Rbm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rbm\Concerns\AppliesRbmListingFilters;
use App\Http\Requests\Rbm\StoreProduitRequest;
use App\Http\Requests\Rbm\UpdateProduitRequest;
use App\Models\Produit;
use App\Models\AxeStrategique;
use App\Services\Rbm\RbmReferentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProduitController extends Controller
{
    use AppliesRbmListingFilters;

    public function __construct(protected RbmReferentialService $rbmService)
    {
        $this->authorizeResource(Produit::class, 'produit');
    }

    public function index(Request $request): View
    {
        $query = Produit::query()->with('axeStrategique')->withCount('sousProduits');
        if ($request->filled('axe_strategique_id')) {
            $query->where('axe_strategique_id', $request->integer('axe_strategique_id'));
        }
        $produits = $this->applyListingFilters($query, $request)->paginate(15)->withQueryString();
        $axeStrategiques = AxeStrategique::query()->orderBy('libelle')->get();

        return view('rbm.produits.index', compact('produits', 'axeStrategiques'));
    }

    public function create(): View
    {
        $axeStrategiques = AxeStrategique::query()->orderBy('libelle')->get();

        return view('rbm.produits.create', compact('axeStrategiques'));
    }

    public function store(StoreProduitRequest $request): RedirectResponse
    {
        $this->rbmService->create(Produit::class, $request->validated(), $request->user());

        return redirect()->route('rbm.produits.index')->with('status', __('Enregistrement créé.'));
    }

    public function show(Produit $produit): View
    {
        $produit->load('axeStrategique')->loadCount('sousProduits');

        return view('rbm.produits.show', compact('produit'));
    }

    public function edit(Produit $produit): View
    {
        $axeStrategiques = AxeStrategique::query()->orderBy('libelle')->get();

        return view('rbm.produits.edit', compact('produit', 'axeStrategiques'));
    }

    public function update(UpdateProduitRequest $request, Produit $produit): RedirectResponse
    {
        $this->rbmService->update($produit, $request->validated(), $request->user());

        return redirect()->route('rbm.produits.index')->with('status', __('Enregistrement mis à jour.'));
    }

    public function destroy(Produit $produit): RedirectResponse
    {
        $this->rbmService->archive($produit, request()->user());

        return redirect()->route('rbm.produits.index')->with('status', __('Enregistrement archivé.'));
    }
}
