<?php

namespace App\Http\Controllers\Liquidation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Liquidation\StoreReceptionRequest;
use App\Http\Requests\Liquidation\StoreServiceFaitRequest;
use App\Models\Liquidation;
use App\Services\Liquidation\LiquidationServiceFaitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LiquidationServiceFaitController extends Controller
{
    public function __construct(private readonly LiquidationServiceFaitService $service)
    {
    }

    public function store(StoreServiceFaitRequest $request, Liquidation $liquidation): RedirectResponse
    {
        $this->authorize('modifier', $liquidation);

        $this->service->enregistrer($liquidation, $request->validated(), $request->user());

        return back()->with('status', __('Service fait constaté.'));
    }

    public function valider(Request $request, Liquidation $liquidation): RedirectResponse
    {
        $this->authorize('modifier', $liquidation);

        $serviceFait = $liquidation->serviceFait;
        if ($serviceFait === null) {
            throw ValidationException::withMessages([
                'service_fait' => __('Aucun service fait à valider.'),
            ]);
        }

        $this->service->valider($serviceFait, $request->user());

        return back()->with('status', __('Service fait validé.'));
    }

    public function leverReserves(Request $request, Liquidation $liquidation): RedirectResponse
    {
        $this->authorize('modifier', $liquidation);

        $request->validate(['observations' => ['nullable', 'string', 'max:2000']]);

        $serviceFait = $liquidation->serviceFait;
        if ($serviceFait === null) {
            throw ValidationException::withMessages([
                'service_fait' => __('Aucun service fait concerné.'),
            ]);
        }

        $this->service->leverReserves($serviceFait, $request->user(), $request->input('observations'));

        return back()->with('status', __('Réserves levées.'));
    }

    public function storeReception(StoreReceptionRequest $request, Liquidation $liquidation): RedirectResponse
    {
        $this->authorize('modifier', $liquidation);

        $this->service->enregistrerReception($liquidation, $request->validated(), $request->user());

        return back()->with('status', __('Réception enregistrée.'));
    }
}
