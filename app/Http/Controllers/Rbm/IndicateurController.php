<?php

namespace App\Http\Controllers\Rbm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rbm\Concerns\AppliesRbmListingFilters;
use App\Http\Requests\Rbm\StoreIndicateurRequest;
use App\Http\Requests\Rbm\UpdateIndicateurRequest;
use App\Models\Indicateur;
use App\Models\SourceVerification;
use App\Models\Tache;
use App\Models\TypeIndicateur;
use App\Models\UniteMesure;
use App\Services\Rbm\RbmReferentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndicateurController extends Controller
{
    use AppliesRbmListingFilters;

    public function __construct(protected RbmReferentialService $rbmService)
    {
        $this->authorizeResource(Indicateur::class, 'indicateur');
    }

    public function index(Request $request): View
    {
        $query = Indicateur::query()
            ->with(['typeIndicateur', 'uniteMesure'])
            ->withCount('taches');

        $indicateurs = $this->applyListingFilters($query, $request)->paginate(15)->withQueryString();

        return view('rbm.indicateurs.index', compact('indicateurs'));
    }

    public function create(): View
    {
        return view('rbm.indicateurs.create', $this->formOptions());
    }

    public function store(StoreIndicateurRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $tacheIds = $data['tache_ids'] ?? [];
        $sourceIds = $data['source_verification_ids'] ?? [];
        unset($data['tache_ids'], $data['source_verification_ids']);

        $indicateur = $this->rbmService->create(Indicateur::class, $data, $request->user());
        $indicateur->taches()->sync($tacheIds);
        $indicateur->sourcesVerification()->sync($sourceIds);

        return redirect()->route('rbm.indicateurs.index')->with('status', __('Indicateur créé.'));
    }

    public function show(Indicateur $indicateur): View
    {
        $indicateur->load(['typeIndicateur', 'uniteMesure', 'taches', 'sourcesVerification']);

        return view('rbm.indicateurs.show', compact('indicateur'));
    }

    public function edit(Indicateur $indicateur): View
    {
        $indicateur->load(['taches', 'sourcesVerification']);

        return view('rbm.indicateurs.edit', array_merge($this->formOptions(), compact('indicateur')));
    }

    public function update(UpdateIndicateurRequest $request, Indicateur $indicateur): RedirectResponse
    {
        $data = $request->validated();
        $tacheIds = $data['tache_ids'] ?? [];
        $sourceIds = $data['source_verification_ids'] ?? [];
        unset($data['tache_ids'], $data['source_verification_ids']);

        $this->rbmService->update($indicateur, $data, $request->user());
        $indicateur->taches()->sync($tacheIds);
        $indicateur->sourcesVerification()->sync($sourceIds);

        return redirect()->route('rbm.indicateurs.index')->with('status', __('Indicateur mis à jour.'));
    }

    public function destroy(Indicateur $indicateur): RedirectResponse
    {
        $this->rbmService->archive($indicateur, request()->user());

        return redirect()->route('rbm.indicateurs.index')->with('status', __('Indicateur archivé.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function formOptions(): array
    {
        return [
            'typesIndicateurs' => TypeIndicateur::query()->orderBy('libelle')->get(),
            'unitesMesure' => UniteMesure::query()->orderBy('libelle')->get(),
            'taches' => Tache::query()->orderBy('libelle')->get(),
            'sourcesVerification' => SourceVerification::query()->orderBy('libelle')->get(),
        ];
    }
}
