<?php

namespace App\Http\Controllers\Ordonnancement;

use App\Http\Controllers\Controller;
use App\Models\ExerciceBudgetaire;
use App\Models\Ordonnancement;
use App\Services\Ordonnancement\OrdonnancementReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrdonnancementDashboardController extends Controller
{
    public function __construct(private readonly OrdonnancementReportingService $reporting)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Ordonnancement::class);

        $exerciceId = $request->integer('exercice_budgetaire_id') ?: null;

        return view('ordonnancements.dashboard', [
            'kpis' => $this->reporting->kpis($exerciceId),
            'parType' => $this->reporting->parType($exerciceId),
            'parStatut' => $this->reporting->parStatut($exerciceId),
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'exerciceId' => $exerciceId,
        ]);
    }
}
