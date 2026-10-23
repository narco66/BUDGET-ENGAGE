<?php

namespace App\Http\Controllers\Paiement;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Models\PaiementType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaiementTypeController extends Controller
{
    public function index(): View
    {
        $this->authorize('gererTypes', Paiement::class);

        $types = PaiementType::query()->orderBy('ordre')->orderBy('libelle')->get();

        return view('paiements.types.index', compact('types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('gererTypes', Paiement::class);

        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;
        PaiementType::query()->create($data);

        return back()->with('status', __('Type de paiement créé.'));
    }

    public function update(Request $request, PaiementType $paiementType): RedirectResponse
    {
        $this->authorize('gererTypes', Paiement::class);

        $data = $this->validateData($request, $paiementType);
        $data['updated_by'] = $request->user()->id;
        $paiementType->update($data);

        return back()->with('status', __('Type de paiement mis à jour.'));
    }

    public function destroy(PaiementType $paiementType): RedirectResponse
    {
        $this->authorize('gererTypes', Paiement::class);

        $paiementType->delete();

        return back()->with('status', __('Type de paiement supprimé.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?PaiementType $type = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:60', Rule::unique('paiement_types', 'code')->ignore($type?->id)],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'famille' => ['nullable', 'string', 'max:60'],
            'nature' => ['nullable', 'string', 'max:60'],
            'permet_generation' => ['nullable', 'boolean'],
            'permet_creation_manuelle' => ['nullable', 'boolean'],
            'exige_banque' => ['nullable', 'boolean'],
            'multi_devises' => ['nullable', 'boolean'],
            'est_actif' => ['nullable', 'boolean'],
            'ordre' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
