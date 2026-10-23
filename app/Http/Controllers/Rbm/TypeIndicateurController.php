<?php

namespace App\Http\Controllers\Rbm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rbm\Concerns\AppliesRbmListingFilters;
use App\Http\Requests\Rbm\StoreTypeIndicateurRequest;
use App\Http\Requests\Rbm\UpdateTypeIndicateurRequest;
use App\Models\TypeIndicateur;
use App\Services\Rbm\RbmReferentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TypeIndicateurController extends Controller
{
    use AppliesRbmListingFilters;

    public function __construct(protected RbmReferentialService $rbmService)
    {
        $this->authorizeResource(TypeIndicateur::class, 'type_indicateur');
    }

    public function index(Request $request): View
    {
        $types_indicateurs = $this->applyListingFilters(
            TypeIndicateur::query()->withCount('indicateurs'),
            $request
        )->paginate(15)->withQueryString();

        return view('rbm.types-indicateurs.index', ['items' => $types_indicateurs]);
    }

    public function create(): View
    {
        return view('rbm.types-indicateurs.create');
    }

    public function store(StoreTypeIndicateurRequest $request): RedirectResponse
    {
        $this->rbmService->create(TypeIndicateur::class, $request->validated(), $request->user());

        return redirect()->route('rbm.types-indicateurs.index')->with('status', __('Enregistrement créé.'));
    }

    public function show(TypeIndicateur $type_indicateur): View
    {
        $type_indicateur->loadCount('indicateurs');

        return view('rbm.types-indicateurs.show', compact('type_indicateur'));
    }

    public function edit(TypeIndicateur $type_indicateur): View
    {
        return view('rbm.types-indicateurs.edit', compact('type_indicateur'));
    }

    public function update(UpdateTypeIndicateurRequest $request, TypeIndicateur $type_indicateur): RedirectResponse
    {
        $this->rbmService->update($type_indicateur, $request->validated(), $request->user());

        return redirect()->route('rbm.types-indicateurs.index')->with('status', __('Enregistrement mis à jour.'));
    }

    public function destroy(TypeIndicateur $type_indicateur): RedirectResponse
    {
        $this->rbmService->archive($type_indicateur, request()->user());

        return redirect()->route('rbm.types-indicateurs.index')->with('status', __('Enregistrement archivé.'));
    }
}
