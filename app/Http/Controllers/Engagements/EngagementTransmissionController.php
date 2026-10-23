<?php

namespace App\Http\Controllers\Engagements;

use App\Http\Controllers\Controller;
use App\Models\Engagement;
use App\Services\Engagement\EngagementToLiquidationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EngagementTransmissionController extends Controller
{
    public function __construct(private readonly EngagementToLiquidationService $service)
    {
    }

    public function transmit(Request $request, Engagement $engagement): RedirectResponse
    {
        abort_unless($request->user()?->can('engagement.transfer_liquidation'), 403);

        $liquidations = $this->service->transmit($engagement, $request->user());

        return redirect()
            ->route('engagements.show', $engagement)
            ->with('status', __(':n liquidation(s) générée(s). Engagement transmis à la liquidation.', ['n' => count($liquidations)]));
    }
}
