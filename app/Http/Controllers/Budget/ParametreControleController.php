<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\StoreParametreControleRequest;
use App\Http\Requests\Budget\UpdateParametreControleRequest;
use App\Models\ParametreControle;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ParametreControleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ParametreControle::class, 'parametre_controle');
    }

    public function index(): View
    {
        $parametres = ParametreControle::query()->orderBy('cle')->paginate(20);

        return view('budget.parametres.index', compact('parametres'));
    }

    public function create(): View
    {
        return view('budget.parametres.create');
    }

    public function store(StoreParametreControleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['actif'] = $request->boolean('actif');
        $data['created_by'] = $request->user()->id;

        ParametreControle::query()->create($data);

        return redirect()->route('budget.parametres.index')->with('status', __('Paramètre créé.'));
    }

    public function show(ParametreControle $parametre_controle): View
    {
        return view('budget.parametres.show', compact('parametre_controle'));
    }

    public function edit(ParametreControle $parametre_controle): View
    {
        return view('budget.parametres.edit', compact('parametre_controle'));
    }

    public function update(UpdateParametreControleRequest $request, ParametreControle $parametre_controle): RedirectResponse
    {
        $data = $request->validated();
        $data['actif'] = $request->boolean('actif');
        $data['updated_by'] = $request->user()->id;

        $parametre_controle->update($data);

        return redirect()->route('budget.parametres.index')->with('status', __('Paramètre mis à jour.'));
    }

    public function destroy(ParametreControle $parametre_controle): RedirectResponse
    {
        $parametre_controle->delete();

        return redirect()->route('budget.parametres.index')->with('status', __('Paramètre supprimé.'));
    }
}
