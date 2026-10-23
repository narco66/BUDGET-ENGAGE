<?php

namespace App\Http\Controllers\Rbm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rbm\Concerns\AppliesRbmListingFilters;
use App\Http\Requests\Rbm\StoreTacheRequest;
use App\Http\Requests\Rbm\UpdateTacheRequest;
use App\Models\Tache;
use App\Models\Activite;
use App\Services\Rbm\RbmReferentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TacheController extends Controller
{
    use AppliesRbmListingFilters;

    public function __construct(protected RbmReferentialService $rbmService)
    {
        $this->authorizeResource(Tache::class, 'tache');
    }

    public function index(Request $request): View
    {
        $query = Tache::query()->with('activite')->withCount('indicateurs');
        if ($request->filled('activite_id')) {
            $query->where('activite_id', $request->integer('activite_id'));
        }
        $taches = $this->applyListingFilters($query, $request)->paginate(15)->withQueryString();
        $activites = Activite::query()->orderBy('libelle')->get();

        return view('rbm.taches.index', compact('taches', 'activites'));
    }

    public function create(): View
    {
        $activites = Activite::query()->orderBy('libelle')->get();

        return view('rbm.taches.create', compact('activites'));
    }

    public function store(StoreTacheRequest $request): RedirectResponse
    {
        $this->rbmService->create(Tache::class, $request->validated(), $request->user());

        return redirect()->route('rbm.taches.index')->with('status', __('Enregistrement créé.'));
    }

    public function show(Tache $tache): View
    {
        $tache->load('activite')->loadCount('indicateurs');

        return view('rbm.taches.show', compact('tache'));
    }

    public function edit(Tache $tache): View
    {
        $activites = Activite::query()->orderBy('libelle')->get();

        return view('rbm.taches.edit', compact('tache', 'activites'));
    }

    public function update(UpdateTacheRequest $request, Tache $tache): RedirectResponse
    {
        $this->rbmService->update($tache, $request->validated(), $request->user());

        return redirect()->route('rbm.taches.index')->with('status', __('Enregistrement mis à jour.'));
    }

    public function destroy(Tache $tache): RedirectResponse
    {
        $this->rbmService->archive($tache, request()->user());

        return redirect()->route('rbm.taches.index')->with('status', __('Enregistrement archivé.'));
    }
}
