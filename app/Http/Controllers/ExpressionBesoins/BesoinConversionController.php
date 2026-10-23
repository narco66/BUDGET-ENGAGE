<?php

namespace App\Http\Controllers\ExpressionBesoins;

use App\Http\Controllers\Controller;
use App\Models\ExpressionBesoin;
use App\Services\ExpressionBesoin\ExpressionBesoinToEngagementService;
use Illuminate\Http\RedirectResponse;

class BesoinConversionController extends Controller
{
    public function __construct(private readonly ExpressionBesoinToEngagementService $converter)
    {
    }

    public function convert(ExpressionBesoin $expression_besoin): RedirectResponse
    {
        $this->authorize('convert', $expression_besoin);

        $engagement = $this->converter->convert($expression_besoin, request()->user());

        return redirect()
            ->route('engagements.show', $engagement)
            ->with('success', __('Besoin converti en engagement :num.', ['num' => $engagement->numero]));
    }
}
