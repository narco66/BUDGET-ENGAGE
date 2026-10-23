<?php

namespace App\Http\Controllers\Rbm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rbm\Concerns\AppliesRbmListingFilters;
use App\Http\Requests\Rbm\StoreActiviteRequest;
use App\Http\Requests\Rbm\UpdateActiviteRequest;
use App\Models\Activite;
use App\Models\SousProduit;
use App\Services\Rbm\RbmReferentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActiviteController extends Controller
{
    use AppliesRbmListingFilters;

    public function __construct(protected RbmReferentialService $rbmService)
    {
        $this->authorizeResource(Activite::class, 'activite');
    }

    public function index(Request $request): View
    {
        $query = Activite::query()->with('sousProduit')->withCount('taches');
        if ($request->filled('sous_produit_id')) {
            $query->where('sous_produit_id', $request->integer('sous_produit_id'));
        }
        $activites = $this->applyListingFilters($query, $request)->paginate(15)->withQueryString();
        $sousProduits = SousProduit::query()->orderBy('libelle')->get();

        return view('rbm.activites.index', compact('activites', 'sousProduits'));
    }

    public function create(): View
    {
        $sousProduits = SousProduit::query()->orderBy('libelle')->get();

        return view('rbm.activites.create', compact('sousProduits'));
    }

    public function store(StoreActiviteRequest $request): RedirectResponse
    {
        $this->rbmService->create(Activite::class, $request->validated(), $request->user());

        return redirect()->route('rbm.activites.index')->with('status', __('Enregistrement créé.'));
    }

    public function show(Activite $activite): View
    {
        $activite->load('sousProduit')->loadCount('taches');

        return view('rbm.activites.show', compact('activite'));
    }

    public function edit(Activite $activite): View
    {
        $sousProduits = SousProduit::query()->orderBy('libelle')->get();

        return view('rbm.activites.edit', compact('activite', 'sousProduits'));
    }

    public function update(UpdateActiviteRequest $request, Activite $activite): RedirectResponse
    {
        $this->rbmService->update($activite, $request->validated(), $request->user());

        return redirect()->route('rbm.activites.index')->with('status', __('Enregistrement mis à jour.'));
    }

    public function destroy(Activite $activite): RedirectResponse
    {
        $this->rbmService->archive($activite, request()->user());

        return redirect()->route('rbm.activites.index')->with('status', __('Enregistrement archivé.'));
    }
}
