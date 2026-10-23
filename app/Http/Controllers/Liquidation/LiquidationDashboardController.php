<?php

namespace App\Http\Controllers\Liquidation;

use App\Http\Controllers\Controller;
use App\Models\ExerciceBudgetaire;
use App\Models\Liquidation;
use App\Services\Liquidation\LiquidationReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiquidationDashboardController extends Controller
{
    public function __construct(private readonly LiquidationReportingService $reporting)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Liquidation::class);

        $exerciceId = $request->integer('exercice_budgetaire_id') ?: null;

        return view('liquidations.dashboard', [
            'kpis' => $this->reporting->kpis($exerciceId),
            'parType' => $this->reporting->parType($exerciceId),
            'parStatut' => $this->reporting->parStatut($exerciceId),
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'exerciceId' => $exerciceId,
        ]);
    }
}
