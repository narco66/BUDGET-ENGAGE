<?php

namespace App\Http\Controllers\Execution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Execution\StoreOrdonnancementRequest;
use App\Models\Liquidation;
use App\Services\EngagementExecutionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrdonnancementController extends Controller
{
    public function create(Liquidation $liquidation): View
    {
        $this->authorize('addOrdonnancement', $liquidation);

        $liquidation->load('engagement');

        return view('execution.ordonnancements.create', compact('liquidation'));
    }

    public function store(StoreOrdonnancementRequest $request, Liquidation $liquidation): RedirectResponse
    {
        app(EngagementExecutionService::class)->recordOrdonnancement(
            $liquidation,
            $request->validated(),
            $request->user()
        );

        $liquidation->load('engagement');

        return redirect()
            ->route('engagements.show', $liquidation->engagement)
            ->with('status', __('execution.messages.ordonnancement_created'));
    }
}
