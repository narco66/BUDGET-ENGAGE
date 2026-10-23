<?php

namespace App\Http\Controllers\Rbm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rbm\Concerns\AppliesRbmListingFilters;
use App\Http\Requests\Rbm\StoreCadreLogiqueRequest;
use App\Http\Requests\Rbm\UpdateCadreLogiqueRequest;
use App\Models\CadreLogique;
use App\Services\Rbm\RbmReferentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CadreLogiqueController extends Controller
{
    use AppliesRbmListingFilters;

    public function __construct(protected RbmReferentialService $rbmService)
    {
        $this->authorizeResource(CadreLogique::class, 'cadre_logique');
    }

    public function index(Request $request): View
    {
        $cadresLogiques = $this->applyListingFilters(
            CadreLogique::query()->withCount('elements'),
            $request
        )->paginate(15)->withQueryString();

        return view('rbm.cadres-logiques.index', compact('cadresLogiques'));
    }

    public function create(): View
    {
        return view('rbm.cadres-logiques.create');
    }

    public function store(StoreCadreLogiqueRequest $request): RedirectResponse
    {
        $this->rbmService->create(CadreLogique::class, $request->validated(), $request->user());

        return redirect()->route('rbm.cadres-logiques.index')->with('status', __('Cadre logique créé.'));
    }

    public function show(CadreLogique $cadreLogique): View
    {
        $cadreLogique->load(['elements.element']);

        return view('rbm.cadres-logiques.show', compact('cadreLogique'));
    }

    public function edit(CadreLogique $cadreLogique): View
    {
        return view('rbm.cadres-logiques.edit', compact('cadreLogique'));
    }

    public function update(UpdateCadreLogiqueRequest $request, CadreLogique $cadreLogique): RedirectResponse
    {
        $this->rbmService->update($cadreLogique, $request->validated(), $request->user());

        return redirect()->route('rbm.cadres-logiques.index')->with('status', __('Cadre logique mis à jour.'));
    }

    public function destroy(CadreLogique $cadreLogique): RedirectResponse
    {
        $this->rbmService->archive($cadreLogique, request()->user());

        return redirect()->route('rbm.cadres-logiques.index')->with('status', __('Cadre logique archivé.'));
    }
}
