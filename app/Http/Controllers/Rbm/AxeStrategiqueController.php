<?php

namespace App\Http\Controllers\Rbm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rbm\Concerns\AppliesRbmListingFilters;
use App\Http\Requests\Rbm\StoreAxeStrategiqueRequest;
use App\Http\Requests\Rbm\UpdateAxeStrategiqueRequest;
use App\Models\AxeStrategique;
use App\Models\Pilier;
use App\Services\Rbm\RbmReferentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AxeStrategiqueController extends Controller
{
    use AppliesRbmListingFilters;

    public function __construct(protected RbmReferentialService $rbmService)
    {
        $this->authorizeResource(AxeStrategique::class, 'axe_strategique');
    }

    public function index(Request $request): View
    {
        $query = AxeStrategique::query()->with('pilier')->withCount('produits');
        if ($request->filled('pilier_id')) {
            $query->where('pilier_id', $request->integer('pilier_id'));
        }
        $axesStrategiques = $this->applyListingFilters($query, $request)->paginate(15)->withQueryString();
        $piliers = Pilier::query()->orderBy('libelle')->get();

        return view('rbm.axes-strategiques.index', compact('axesStrategiques', 'piliers'));
    }

    public function create(Request $request): View
    {
        $piliers = Pilier::query()->orderBy('libelle')->get();

        return view('rbm.axes-strategiques.create', compact('piliers'));
    }

    public function store(StoreAxeStrategiqueRequest $request): RedirectResponse
    {
        $this->rbmService->create(AxeStrategique::class, $request->validated(), $request->user());

        return redirect()->route('rbm.axes-strategiques.index')->with('status', __('Axe stratégique créé.'));
    }

    public function show(AxeStrategique $axeStrategique): View
    {
        $axeStrategique->load('pilier')->loadCount('produits');

        return view('rbm.axes-strategiques.show', compact('axeStrategique'));
    }

    public function edit(AxeStrategique $axeStrategique): View
    {
        $piliers = Pilier::query()->orderBy('libelle')->get();

        return view('rbm.axes-strategiques.edit', compact('axeStrategique', 'piliers'));
    }

    public function update(UpdateAxeStrategiqueRequest $request, AxeStrategique $axeStrategique): RedirectResponse
    {
        $this->rbmService->update($axeStrategique, $request->validated(), $request->user());

        return redirect()->route('rbm.axes-strategiques.index')->with('status', __('Axe stratégique mis à jour.'));
    }

    public function destroy(AxeStrategique $axeStrategique): RedirectResponse
    {
        $this->rbmService->archive($axeStrategique, request()->user());

        return redirect()->route('rbm.axes-strategiques.index')->with('status', __('Axe stratégique archivé.'));
    }
}
