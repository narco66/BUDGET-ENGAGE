<?php

namespace App\Http\Controllers\Rbm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rbm\Concerns\AppliesRbmListingFilters;
use App\Http\Requests\Rbm\StoreChaineResultatRequest;
use App\Http\Requests\Rbm\UpdateChaineResultatRequest;
use App\Models\CadreLogique;
use App\Models\ChaineResultat;
use App\Models\Indicateur;
use App\Services\Rbm\RbmReferentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChaineResultatController extends Controller
{
    use AppliesRbmListingFilters;

    public function __construct(protected RbmReferentialService $rbmService)
    {
        $this->authorizeResource(ChaineResultat::class, 'chaine_resultat');
    }

    public function index(Request $request): View
    {
        $chainesResultats = $this->applyListingFilters(
            ChaineResultat::query()->with(['cadreLogique', 'indicateur']),
            $request
        )->paginate(15)->withQueryString();

        return view('rbm.chaines-resultats.index', compact('chainesResultats'));
    }

    public function create(): View
    {
        return view('rbm.chaines-resultats.create', [
            'cadresLogiques' => CadreLogique::query()->orderBy('libelle')->get(),
            'indicateurs' => Indicateur::query()->orderBy('libelle')->get(),
        ]);
    }

    public function store(StoreChaineResultatRequest $request): RedirectResponse
    {
        $this->rbmService->create(ChaineResultat::class, $request->validated(), $request->user());

        return redirect()->route('rbm.chaines-resultats.index')->with('status', __('Chaîne de résultats créée.'));
    }

    public function show(ChaineResultat $chaineResultat): View
    {
        $chaineResultat->load(['cadreLogique', 'indicateur', 'element']);

        return view('rbm.chaines-resultats.show', compact('chaineResultat'));
    }

    public function edit(ChaineResultat $chaineResultat): View
    {
        return view('rbm.chaines-resultats.edit', [
            'chaineResultat' => $chaineResultat,
            'cadresLogiques' => CadreLogique::query()->orderBy('libelle')->get(),
            'indicateurs' => Indicateur::query()->orderBy('libelle')->get(),
        ]);
    }

    public function update(UpdateChaineResultatRequest $request, ChaineResultat $chaineResultat): RedirectResponse
    {
        $this->rbmService->update($chaineResultat, $request->validated(), $request->user());

        return redirect()->route('rbm.chaines-resultats.index')->with('status', __('Chaîne de résultats mise à jour.'));
    }

    public function destroy(ChaineResultat $chaineResultat): RedirectResponse
    {
        $this->rbmService->archive($chaineResultat, request()->user());

        return redirect()->route('rbm.chaines-resultats.index')->with('status', __('Chaîne de résultats archivée.'));
    }
}
