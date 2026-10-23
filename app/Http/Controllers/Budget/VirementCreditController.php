<?php

namespace App\Http\Controllers\Budget;

use App\Http\Controllers\Controller;
use App\Models\ExerciceBudgetaire;
use App\Models\LigneBudgetaire;
use App\Models\VirementCredit;
use App\Services\Budget\VirementCreditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VirementCreditController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:budget.virement|budget.virement.approve');
        $this->middleware('permission:budget.virement')->only('store');
        $this->middleware('permission:budget.virement.approve')->only(['approve', 'reject']);
    }

    public function index(Request $request): View
    {
        $exercice = $this->resolveExercice($request);

        $lignes = $exercice
            ? LigneBudgetaire::query()->where('exercice_budgetaire_id', $exercice->id)->orderBy('code')->get()
            : collect();

        return view('budget.virements.index', [
            'exercice' => $exercice,
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'lignes' => $lignes,
            'enAttente' => $exercice
                ? VirementCredit::query()->with('source', 'destination', 'user')
                    ->where('exercice_budgetaire_id', $exercice->id)->enAttente()->latest()->get()
                : collect(),
            'virements' => $exercice
                ? VirementCredit::query()->with('source', 'destination', 'user', 'approbateur')
                    ->where('exercice_budgetaire_id', $exercice->id)
                    ->whereIn('statut', ['approuve', 'rejete', 'valide'])
                    ->latest()->limit(30)->get()
                : collect(),
            'peutApprouver' => $request->user()->can('budget.virement.approve'),
        ]);
    }

    public function store(Request $request, VirementCreditService $service): RedirectResponse
    {
        $data = $request->validate([
            'ligne_source_id' => ['required', 'integer', 'exists:lignes_budgetaires,id'],
            'ligne_destination_id' => ['required', 'integer', 'different:ligne_source_id', 'exists:lignes_budgetaires,id'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'motif' => ['required', 'string', 'max:1000'],
        ]);

        $source = LigneBudgetaire::query()->findOrFail($data['ligne_source_id']);
        $destination = LigneBudgetaire::query()->findOrFail($data['ligne_destination_id']);

        $virement = $service->propose($source, $destination, (string) $data['montant'], $data['motif'], $request->user(), $request->ip());

        return redirect()
            ->route('budget.virements.index', ['exercice_budgetaire_id' => $virement->exercice_budgetaire_id])
            ->with('status', __('Virement proposé (#:id) — en attente d\'approbation.', ['id' => $virement->id]));
    }

    public function approve(Request $request, VirementCredit $virement, VirementCreditService $service): RedirectResponse
    {
        $commentaire = $request->validate(['commentaire' => ['nullable', 'string', 'max:1000']])['commentaire'] ?? null;
        $service->approve($virement, $request->user(), $commentaire);

        return redirect()
            ->route('budget.virements.index', ['exercice_budgetaire_id' => $virement->exercice_budgetaire_id])
            ->with('status', __('Virement #:id approuvé et appliqué.', ['id' => $virement->id]));
    }

    public function reject(Request $request, VirementCredit $virement, VirementCreditService $service): RedirectResponse
    {
        $motif = $request->validate(['motif' => ['required', 'string', 'max:1000']])['motif'];
        $service->reject($virement, $request->user(), $motif);

        return redirect()
            ->route('budget.virements.index', ['exercice_budgetaire_id' => $virement->exercice_budgetaire_id])
            ->with('status', __('Virement #:id rejeté.', ['id' => $virement->id]));
    }

    private function resolveExercice(Request $request): ?ExerciceBudgetaire
    {
        $id = $request->integer('exercice_budgetaire_id');

        return ($id > 0 ? ExerciceBudgetaire::query()->find($id) : null)
            ?? ExerciceBudgetaire::query()->where('est_actif', true)->orderByDesc('annee')->first()
            ?? ExerciceBudgetaire::query()->orderByDesc('annee')->first();
    }
}
