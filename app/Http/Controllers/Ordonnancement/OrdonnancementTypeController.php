<?php

namespace App\Http\Controllers\Ordonnancement;

use App\Http\Controllers\Controller;
use App\Models\Ordonnancement;
use App\Models\OrdonnancementType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrdonnancementTypeController extends Controller
{
    public function index(): View
    {
        $this->authorize('gererTypes', Ordonnancement::class);

        $types = OrdonnancementType::query()->orderBy('ordre')->orderBy('libelle')->get();

        return view('ordonnancements.types.index', compact('types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('gererTypes', Ordonnancement::class);

        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;
        OrdonnancementType::query()->create($data);

        return back()->with('status', __('Type d’ordonnancement créé.'));
    }

    public function update(Request $request, OrdonnancementType $ordonnancementType): RedirectResponse
    {
        $this->authorize('gererTypes', Ordonnancement::class);

        $data = $this->validateData($request, $ordonnancementType);
        $data['updated_by'] = $request->user()->id;
        $ordonnancementType->update($data);

        return back()->with('status', __('Type d’ordonnancement mis à jour.'));
    }

    public function destroy(OrdonnancementType $ordonnancementType): RedirectResponse
    {
        $this->authorize('gererTypes', Ordonnancement::class);

        $ordonnancementType->delete();

        return back()->with('status', __('Type d’ordonnancement supprimé.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?OrdonnancementType $type = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:60', Rule::unique('ordonnancement_types', 'code')->ignore($type?->id)],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'famille' => ['nullable', 'string', 'max:60'],
            'nature' => ['nullable', 'string', 'max:60'],
            'permet_generation' => ['nullable', 'boolean'],
            'permet_creation_manuelle' => ['nullable', 'boolean'],
            'exige_mandat' => ['nullable', 'boolean'],
            'est_actif' => ['nullable', 'boolean'],
            'ordre' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
