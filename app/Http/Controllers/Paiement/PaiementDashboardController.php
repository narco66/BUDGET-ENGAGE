<?php

namespace App\Http\Controllers\Paiement;

use App\Http\Controllers\Controller;
use App\Models\ExerciceBudgetaire;
use App\Models\Paiement;
use App\Services\Paiement\PaiementReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaiementDashboardController extends Controller
{
    public function __construct(private readonly PaiementReportingService $reporting)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Paiement::class);

        $exerciceId = $request->integer('exercice_budgetaire_id') ?: null;

        return view('paiements.dashboard', [
            'kpis' => $this->reporting->kpis($exerciceId),
            'parType' => $this->reporting->parType($exerciceId),
            'parBanque' => $this->reporting->parBanque($exerciceId),
            'parStatut' => $this->reporting->parStatut($exerciceId),
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'exerciceId' => $exerciceId,
        ]);
    }
}
