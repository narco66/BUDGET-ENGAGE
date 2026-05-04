<?php

namespace App\Http\Controllers\Referentials;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referentials\StoreDepartementRequest;
use App\Http\Requests\Referentials\UpdateDepartementRequest;
use App\Models\Departement;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartementController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Departement::class, 'departement');
    }

    public function index(): View
    {
        $departements = Departement::query()
            ->withCount('directions')
            ->orderBy('code')
            ->paginate(15);

        return view('referentials.departements.index', compact('departements'));
    }

    public function create(): View
    {
        return view('referentials.departements.create');
    }

    public function store(StoreDepartementRequest $request): RedirectResponse
    {
        Departement::query()->create($request->validated());

        return redirect()->route('referentials.departements.index')->with('status', __('Département créé.'));
    }

    public function show(Departement $departement): View
    {
        $departement->loadCount('directions');

        return view('referentials.departements.show', compact('departement'));
    }

    public function edit(Departement $departement): View
    {
        return view('referentials.departements.edit', compact('departement'));
    }

    public function update(UpdateDepartementRequest $request, Departement $departement): RedirectResponse
    {
        $departement->update($request->validated());

        return redirect()->route('referentials.departements.index')->with('status', __('Département mis à jour.'));
    }

    public function destroy(Departement $departement): RedirectResponse
    {
        $departement->delete();

        return redirect()->route('referentials.departements.index')->with('status', __('Département archivé.'));
    }
}
