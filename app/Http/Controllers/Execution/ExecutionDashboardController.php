<?php

namespace App\Http\Controllers\Execution;

use App\Http\Controllers\Controller;
use App\Models\Liquidation;
use App\Models\Ordonnancement;
use App\Models\Paiement;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ExecutionDashboardController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->can('execution.liquidation') || $request->user()?->can('execution.payment'),
            403
        );

        $tab = $request->string('tab')->toString() ?: 'liquidations';
        $search = $request->string('q')->toString();

        $liquidations = Liquidation::query()
            ->with(['engagement.exerciceBudgetaire', 'engagementImputation.ligneBudgetaire', 'createur'])
            ->when($search !== '', fn ($q) => $q->whereHas('engagement', fn ($eq) => $eq->where('numero', 'like', "%{$search}%")))
            ->latest('date_liquidation')
            ->limit(50)
            ->get();

        $ordonnancements = Ordonnancement::query()
            ->with(['liquidation.engagement', 'engagementImputation.ligneBudgetaire', 'createur'])
            ->when($search !== '', fn ($q) => $q->whereHas('liquidation.engagement', fn ($eq) => $eq->where('numero', 'like', "%{$search}%")))
            ->latest('date_ordonnancement')
            ->limit(50)
            ->get();

        $paiements = Paiement::query()
            ->with(['ordonnancement.liquidation.engagement', 'engagementImputation.ligneBudgetaire', 'createur'])
            ->when($search !== '', fn ($q) => $q->whereHas('ordonnancement.liquidation.engagement', fn ($eq) => $eq->where('numero', 'like', "%{$search}%")))
            ->latest('date_paiement')
            ->limit(50)
            ->get();

        $kpis = [
            'liquidations' => Liquidation::query()->count(),
            'ordonnancements' => Ordonnancement::query()->count(),
            'paiements' => Paiement::query()->count(),
            'montant_liquide' => (float) Liquidation::query()->sum('montant_liquide'),
            'montant_ordonnance' => (float) Ordonnancement::query()->sum('montant_ordonnance'),
            'montant_paye' => (float) Paiement::query()->sum('montant_paye'),
        ];

        return view('execution.dashboard', compact(
            'tab', 'search', 'liquidations', 'ordonnancements', 'paiements', 'kpis'
        ));
    }
}
