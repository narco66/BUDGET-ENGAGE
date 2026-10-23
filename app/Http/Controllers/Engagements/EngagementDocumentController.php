<?php

namespace App\Http\Controllers\Engagements;

use App\Exports\EngagementsCreditsReservesExport;
use App\Exports\EngagementsRegistreExport;
use App\Http\Controllers\Controller;
use App\Models\Engagement;
use App\Models\LigneBudgetaire;
use App\Policies\EngagementPolicy;
use App\Services\Budget\CreditAvailabilityService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EngagementDocumentController extends Controller
{
    public function certificat(Engagement $engagement): Response
    {
        $this->authorize('view', $engagement);

        $engagement->load([
            'exerciceBudgetaire', 'engagementType', 'departement', 'direction', 'serviceStructure',
            'createur', 'imputations.ligneBudgetaire', 'imputations.sourceFinancement', 'imputations.activite',
            'typeValeurs', 'validationsWorkflow.validateur',
        ]);

        return Pdf::loadView('engagements.exports.certificat', [
            'engagement' => $engagement,
            'generatedAt' => now(),
        ])->download('certificat-engagement-'.$engagement->numero.'.pdf');
    }

    public function registrePdf(Request $request): Response
    {
        abort_unless($request->user()?->can('engagement.export'), 403);

        $engagements = $this->query($request)->limit(5000)->get();

        return Pdf::loadView('engagements.exports.registre', [
            'titre' => 'Registre des engagements',
            'engagements' => $engagements,
            'generatedAt' => now(),
        ])->download('registre-engagements.pdf');
    }

    public function registreExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('engagement.export'), 403);

        return Excel::download(new EngagementsRegistreExport($this->query($request)->limit(10000)->get()), 'registre-engagements.xlsx');
    }

    public function bordereauPdf(Request $request): Response
    {
        abort_unless($request->user()?->can('engagement.export'), 403);

        $engagements = $this->query($request)->limit(5000)->get();

        return Pdf::loadView('engagements.exports.bordereau', [
            'engagements' => $engagements,
            'total' => (string) $engagements->sum(fn (Engagement $e) => (float) $e->montant),
            'generatedAt' => now(),
        ])->download('bordereau-engagements.pdf');
    }

    public function creditsReservesPdf(Request $request): Response
    {
        abort_unless($request->user()?->can('engagement.export'), 403);

        return Pdf::loadView('engagements.exports.credits_reserves', [
            'lignes' => $this->creditsData($request),
            'generatedAt' => now(),
        ])->download('etat-credits-reserves.pdf');
    }

    public function creditsReservesExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('engagement.export'), 403);

        $rows = $this->creditsData($request)->map(fn (array $l) => [
            $l['code'], $l['libelle'], $l['annee'],
            number_format((float) $l['vote'], 2, '.', ''),
            number_format((float) $l['revise'], 2, '.', ''),
            number_format((float) $l['engage'], 2, '.', ''),
            number_format((float) $l['disponible'], 2, '.', ''),
        ]);

        return Excel::download(new EngagementsCreditsReservesExport($rows), 'etat-credits-reserves.xlsx');
    }

    /**
     * @return Builder<Engagement>
     */
    private function query(Request $request): Builder
    {
        $query = Engagement::query()
            ->with(['exerciceBudgetaire', 'departement', 'engagementType', 'createur'])
            ->orderByDesc('created_at');

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(fn ($b) => $b->where('numero', 'like', "%{$q}%")->orWhere('objet', 'like', "%{$q}%"));
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }
        if ($request->filled('exercice_budgetaire_id')) {
            $query->where('exercice_budgetaire_id', $request->integer('exercice_budgetaire_id'));
        }

        if (! EngagementPolicy::seesAllEngagements($request->user())) {
            $query->where('created_by', $request->user()->id);
        }

        return $query;
    }

    /**
     * État des crédits réservés par ligne budgétaire.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function creditsData(Request $request): Collection
    {
        $credits = app(CreditAvailabilityService::class);

        return LigneBudgetaire::query()
            ->with('exerciceBudgetaire')
            ->when($request->filled('exercice_budgetaire_id'), fn ($q) => $q->where('exercice_budgetaire_id', $request->integer('exercice_budgetaire_id')))
            ->where('montant_engage', '>', 0)
            ->orderByDesc('montant_engage')
            ->limit(5000)
            ->get()
            ->map(fn (LigneBudgetaire $ligne) => [
                'code' => $ligne->code,
                'libelle' => $ligne->libelle,
                'annee' => (string) ($ligne->exerciceBudgetaire?->annee ?? ''),
                'vote' => (string) $ligne->montant_vote,
                'revise' => (string) $ligne->montant_revise,
                'engage' => (string) $ligne->montant_engage,
                'disponible' => $credits->disponibleComptable($ligne),
            ])
            ->values();
    }
}
