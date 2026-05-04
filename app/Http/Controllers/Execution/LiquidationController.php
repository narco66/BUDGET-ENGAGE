<?php

namespace App\Http\Controllers\Execution;

use App\Http\Controllers\Controller;
use App\Http\Requests\Execution\StoreLiquidationRequest;
use App\Models\Engagement;
use App\Services\EngagementExecutionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LiquidationController extends Controller
{
    public function create(Engagement $engagement): View
    {
        $this->authorize('createLiquidation', $engagement);

        return view('execution.liquidations.create', compact('engagement'));
    }

    public function store(StoreLiquidationRequest $request, Engagement $engagement): RedirectResponse
    {
        app(EngagementExecutionService::class)->recordLiquidation(
            $engagement,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('engagements.show', $engagement->fresh())
            ->with('status', __('execution.messages.liquidation_created'));
    }
}
