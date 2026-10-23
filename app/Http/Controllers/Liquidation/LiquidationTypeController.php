<?php

namespace App\Http\Controllers\Liquidation;

use App\Http\Controllers\Controller;
use App\Models\LiquidationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LiquidationTypeController extends Controller
{
    public function index(): View
    {
        $this->authorize('gererTypes', \App\Models\Liquidation::class);

        $types = LiquidationType::query()->orderBy('ordre')->orderBy('libelle')->get();

        return view('liquidations.types.index', compact('types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('gererTypes', \App\Models\Liquidation::class);

        $data = $this->validateData($request);
        $data['created_by'] = $request->user()->id;
        LiquidationType::query()->create($data);

        return back()->with('status', __('Type de liquidation créé.'));
    }

    public function update(Request $request, LiquidationType $liquidationType): RedirectResponse
    {
        $this->authorize('gererTypes', \App\Models\Liquidation::class);

        $data = $this->validateData($request, $liquidationType);
        $data['updated_by'] = $request->user()->id;
        $liquidationType->update($data);

        return back()->with('status', __('Type de liquidation mis à jour.'));
    }

    public function destroy(LiquidationType $liquidationType): RedirectResponse
    {
        $this->authorize('gererTypes', \App\Models\Liquidation::class);

        $liquidationType->delete();

        return back()->with('status', __('Type de liquidation supprimé.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validateData(Request $request, ?LiquidationType $type = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:60', Rule::unique('liquidation_types', 'code')->ignore($type?->id)],
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'famille' => ['nullable', 'string', 'max:60'],
            'nature' => ['nullable', 'string', 'max:60'],
            'permet_generation' => ['nullable', 'boolean'],
            'permet_creation_manuelle' => ['nullable', 'boolean'],
            'exige_service_fait' => ['nullable', 'boolean'],
            'exige_reception' => ['nullable', 'boolean'],
            'est_actif' => ['nullable', 'boolean'],
            'ordre' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
