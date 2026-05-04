<?php

namespace App\Http\Controllers\Referentials;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referentials\StoreFournisseurRequest;
use App\Http\Requests\Referentials\UpdateFournisseurRequest;
use App\Models\Fournisseur;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FournisseurController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Fournisseur::class, 'fournisseur');
    }

    public function index(): View
    {
        $fournisseurs = Fournisseur::query()->orderBy('raison_sociale')->paginate(15);

        return view('referentials.fournisseurs.index', compact('fournisseurs'));
    }

    public function create(): View
    {
        return view('referentials.fournisseurs.create');
    }

    public function store(StoreFournisseurRequest $request): RedirectResponse
    {
        Fournisseur::query()->create($request->validated());

        return redirect()->route('referentials.fournisseurs.index')->with('status', __('Fournisseur créé.'));
    }

    public function show(Fournisseur $fournisseur): View
    {
        return view('referentials.fournisseurs.show', compact('fournisseur'));
    }

    public function edit(Fournisseur $fournisseur): View
    {
        return view('referentials.fournisseurs.edit', compact('fournisseur'));
    }

    public function update(UpdateFournisseurRequest $request, Fournisseur $fournisseur): RedirectResponse
    {
        $fournisseur->update($request->validated());

        return redirect()->route('referentials.fournisseurs.index')->with('status', __('Fournisseur mis à jour.'));
    }

    public function destroy(Fournisseur $fournisseur): RedirectResponse
    {
        $fournisseur->delete();

        return redirect()->route('referentials.fournisseurs.index')->with('status', __('Fournisseur archivé.'));
    }
}
