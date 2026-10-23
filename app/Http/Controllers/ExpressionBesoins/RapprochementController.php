<?php

namespace App\Http\Controllers\ExpressionBesoins;

use App\Exports\BesoinRapprochementExport;
use App\Http\Controllers\Controller;
use App\Models\ExpressionBesoin;
use App\Policies\ExpressionBesoinPolicy;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Rapprochement de la chaîne : Expression de besoin → Engagement → Liquidation.
 * Permet de suivre, pour chaque besoin converti, le montant engagé et le montant
 * effectivement liquidé, ainsi que l'écart.
 */
class RapprochementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ExpressionBesoin::class);

        $besoins = $this->query($request)->paginate(20);

        return view('expression_besoins.rapprochement', compact('besoins'));
    }

    public function excel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('expression_besoins.export'), 403);

        $rows = $this->query($request)->limit(10000)->get()->map(function (ExpressionBesoin $besoin): array {
            $engagement = $besoin->engagement;
            $liquide = $engagement ? (float) $engagement->liquidations->sum(fn ($l) => (float) $l->montant_liquide) : 0.0;
            $engage = $engagement ? (float) $engagement->montant : 0.0;

            return [
                (string) $besoin->reference,
                (string) ($engagement?->numero ?? ''),
                number_format((float) $besoin->montant_total, 2, '.', ''),
                number_format($engage, 2, '.', ''),
                number_format($liquide, 2, '.', ''),
                number_format($engage - $liquide, 2, '.', ''),
                $engagement ? ($engagement->statut instanceof \BackedEnum ? $engagement->statut->value : (string) $engagement->statut) : '',
            ];
        });

        return Excel::download(new BesoinRapprochementExport($rows), 'rapprochement-besoins.xlsx');
    }

    /**
     * @return Builder<ExpressionBesoin>
     */
    private function query(Request $request): Builder
    {
        $query = ExpressionBesoin::query()
            ->whereNotNull('engagement_id')
            ->with(['engagement.liquidations', 'exerciceBudgetaire'])
            ->orderByDesc('converted_at');

        if (! ExpressionBesoinPolicy::seesAll($request->user())) {
            $query->where(fn ($b) => $b->where('created_by', $request->user()->id)->orWhere('demandeur_id', $request->user()->id));
        }

        return $query;
    }
}
