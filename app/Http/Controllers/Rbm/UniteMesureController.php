<?php

namespace App\Http\Controllers\Rbm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rbm\Concerns\AppliesRbmListingFilters;
use App\Http\Requests\Rbm\StoreUniteMesureRequest;
use App\Http\Requests\Rbm\UpdateUniteMesureRequest;
use App\Models\UniteMesure;
use App\Services\Rbm\RbmReferentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UniteMesureController extends Controller
{
    use AppliesRbmListingFilters;

    public function __construct(protected RbmReferentialService $rbmService)
    {
        $this->authorizeResource(UniteMesure::class, 'unite_mesure');
    }

    public function index(Request $request): View
    {
        $unites_mesure = $this->applyListingFilters(
            UniteMesure::query()->withCount('indicateurs'),
            $request
        )->paginate(15)->withQueryString();

        return view('rbm.unites-mesure.index', ['items' => $unites_mesure]);
    }

    public function create(): View
    {
        return view('rbm.unites-mesure.create');
    }

    public function store(StoreUniteMesureRequest $request): RedirectResponse
    {
        $this->rbmService->create(UniteMesure::class, $request->validated(), $request->user());

        return redirect()->route('rbm.unites-mesure.index')->with('status', __('Enregistrement créé.'));
    }

    public function show(UniteMesure $unite_mesure): View
    {
        $unite_mesure->loadCount('indicateurs');

        return view('rbm.unites-mesure.show', compact('unite_mesure'));
    }

    public function edit(UniteMesure $unite_mesure): View
    {
        return view('rbm.unites-mesure.edit', compact('unite_mesure'));
    }

    public function update(UpdateUniteMesureRequest $request, UniteMesure $unite_mesure): RedirectResponse
    {
        $this->rbmService->update($unite_mesure, $request->validated(), $request->user());

        return redirect()->route('rbm.unites-mesure.index')->with('status', __('Enregistrement mis à jour.'));
    }

    public function destroy(UniteMesure $unite_mesure): RedirectResponse
    {
        $this->rbmService->archive($unite_mesure, request()->user());

        return redirect()->route('rbm.unites-mesure.index')->with('status', __('Enregistrement archivé.'));
    }
}
