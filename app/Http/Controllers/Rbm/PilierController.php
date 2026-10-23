<?php

namespace App\Http\Controllers\Rbm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rbm\Concerns\AppliesRbmListingFilters;
use App\Http\Requests\Rbm\StorePilierRequest;
use App\Http\Requests\Rbm\UpdatePilierRequest;
use App\Models\Pilier;
use App\Services\Rbm\RbmReferentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PilierController extends Controller
{
    use AppliesRbmListingFilters;

    public function __construct(
        protected RbmReferentialService $rbmService
    ) {
        $this->authorizeResource(Pilier::class, 'pilier');
    }

    public function index(Request $request): View
    {
        $piliers = $this->applyListingFilters(
            Pilier::query()->withCount('axesStrategiques'),
            $request
        )->paginate(15)->withQueryString();

        return view('rbm.piliers.index', compact('piliers'));
    }

    public function create(): View
    {
        return view('rbm.piliers.create');
    }

    public function store(StorePilierRequest $request): RedirectResponse
    {
        $this->rbmService->create(Pilier::class, $request->validated(), $request->user());

        return redirect()->route('rbm.piliers.index')->with('status', __('Pilier créé.'));
    }

    public function show(Pilier $pilier): View
    {
        $pilier->loadCount('axesStrategiques');

        return view('rbm.piliers.show', compact('pilier'));
    }

    public function edit(Pilier $pilier): View
    {
        return view('rbm.piliers.edit', compact('pilier'));
    }

    public function update(UpdatePilierRequest $request, Pilier $pilier): RedirectResponse
    {
        $this->rbmService->update($pilier, $request->validated(), $request->user());

        return redirect()->route('rbm.piliers.index')->with('status', __('Pilier mis à jour.'));
    }

    public function destroy(Pilier $pilier): RedirectResponse
    {
        $this->rbmService->archive($pilier, request()->user());

        return redirect()->route('rbm.piliers.index')->with('status', __('Pilier archivé.'));
    }

    public function restore(int $pilier): RedirectResponse
    {
        $model = Pilier::withTrashed()->findOrFail($pilier);
        $this->authorize('restore', $model);
        $this->rbmService->restore($model, request()->user());

        return redirect()->route('rbm.piliers.index')->with('status', __('Pilier restauré.'));
    }
}
