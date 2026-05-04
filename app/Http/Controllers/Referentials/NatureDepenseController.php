<?php

namespace App\Http\Controllers\Referentials;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referentials\StoreNatureDepenseRequest;
use App\Http\Requests\Referentials\UpdateNatureDepenseRequest;
use App\Models\NatureDepense;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NatureDepenseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(NatureDepense::class, 'nature_depense');
    }

    public function index(): View
    {
        $natures = NatureDepense::query()->orderBy('code')->paginate(15);

        return view('referentials.natures_depenses.index', compact('natures'));
    }

    public function create(): View
    {
        return view('referentials.natures_depenses.create');
    }

    public function store(StoreNatureDepenseRequest $request): RedirectResponse
    {
        NatureDepense::query()->create($request->validated());

        return redirect()->route('referentials.natures-depenses.index')->with('status', __('Nature de dépense créée.'));
    }

    public function show(NatureDepense $nature_depense): View
    {
        return view('referentials.natures_depenses.show', ['nature' => $nature_depense]);
    }

    public function edit(NatureDepense $nature_depense): View
    {
        return view('referentials.natures_depenses.edit', ['nature' => $nature_depense]);
    }

    public function update(UpdateNatureDepenseRequest $request, NatureDepense $nature_depense): RedirectResponse
    {
        $nature_depense->update($request->validated());

        return redirect()->route('referentials.natures-depenses.index')->with('status', __('Nature de dépense mise à jour.'));
    }

    public function destroy(NatureDepense $nature_depense): RedirectResponse
    {
        $nature_depense->delete();

        return redirect()->route('referentials.natures-depenses.index')->with('status', __('Nature de dépense supprimée.'));
    }
}
