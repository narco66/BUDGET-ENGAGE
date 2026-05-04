<?php

namespace App\Http\Controllers\Engagements;

use App\Enums\DecisionWorkflow;
use App\Enums\StatutEngagement;
use App\Http\Controllers\Controller;
use App\Http\Requests\Engagements\CancelApprovedEngagementRequest;
use App\Http\Requests\Engagements\StoreEngagementRequest;
use App\Http\Requests\Engagements\UpdateEngagementRequest;
use App\Http\Requests\Engagements\WorkflowDecisionRequest;
use App\Models\Agent;
use App\Models\Departement;
use App\Models\Direction;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\Fournisseur;
use App\Models\LigneBudgetaire;
use App\Models\Service;
use App\Policies\EngagementPolicy;
use App\Services\EngagementCancellationService;
use App\Services\EngagementWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EngagementController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Engagement::class, 'engagement');
    }

    public function index(Request $request): View
    {
        $query = Engagement::query()
            ->with([
                'exerciceBudgetaire',
                'ligneBudgetaire',
                'departement',
                'createur',
            ])
            ->orderByDesc('created_at');

        if (! EngagementPolicy::seesAllEngagements($request->user())) {
            $query->where('created_by', $request->user()->id);
        }

        $engagements = $query->paginate(20);

        return view('engagements.index', compact('engagements'));
    }

    public function create(): View
    {
        return view('engagements.create', $this->formDependencies());
    }

    public function store(StoreEngagementRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $this->assertMontantWithinCredit(
            (int) $validated['ligne_budgetaire_id'],
            (string) $validated['montant'],
            null
        );

        $exercice = ExerciceBudgetaire::query()->findOrFail((int) $validated['exercice_budgetaire_id']);

        $validated['numero'] = $this->generateUniqueNumero($exercice->annee);
        $validated['statut'] = StatutEngagement::Brouillon;
        $validated['created_by'] = $request->user()->id;

        Engagement::query()->create($validated);

        return redirect()->route('engagements.index')->with('status', __('Engagement enregistré en brouillon.'));
    }

    public function show(Engagement $engagement): View
    {
        $engagement->load([
            'exerciceBudgetaire',
            'ligneBudgetaire.natureDepense',
            'departement',
            'direction',
            'serviceStructure',
            'createur',
            'validationsWorkflow.validateur',
            'liquidations.createur',
            'liquidations.ordonnancements.createur',
            'liquidations.ordonnancements.paiements.createur',
        ]);

        return view('engagements.show', compact('engagement'));
    }

    public function edit(Engagement $engagement): View
    {
        return view('engagements.edit', array_merge(
            ['engagement' => $engagement],
            $this->formDependencies()
        ));
    }

    public function update(UpdateEngagementRequest $request, Engagement $engagement): RedirectResponse
    {
        $validated = $request->validated();
        $this->assertMontantWithinCredit(
            (int) $validated['ligne_budgetaire_id'],
            (string) $validated['montant'],
            $engagement
        );

        $engagement->update($validated);

        return redirect()->route('engagements.index')->with('status', __('Engagement mis à jour.'));
    }

    public function destroy(Engagement $engagement): RedirectResponse
    {
        $engagement->delete();

        return redirect()->route('engagements.index')->with('status', __('Engagement supprimé.'));
    }

    public function submit(Request $request, Engagement $engagement): RedirectResponse
    {
        $this->authorize('submit', $engagement);

        if ($engagement->is_urgence && blank($engagement->justification_urgence)) {
            throw ValidationException::withMessages([
                'justification_urgence' => __('Une justification est requise pour une procédure d’urgence.'),
            ]);
        }

        app(EngagementWorkflowService::class)->submitForValidation($engagement, $request->user());

        return redirect()->route('engagements.show', $engagement->fresh())->with('status', __('Engagement transmis en circuit de validation.'));
    }

    public function workflowDecision(WorkflowDecisionRequest $request, Engagement $engagement): RedirectResponse
    {
        $decision = DecisionWorkflow::from($request->validated('decision'));

        app(EngagementWorkflowService::class)->processDecision(
            $engagement,
            $request->user(),
            $decision,
            $request->validated('commentaire')
        );

        $engagement->refresh();

        $statusMessage = match ($decision) {
            DecisionWorkflow::Valide => $engagement->statut === StatutEngagement::Approuve
                ? __('workflow.messages.approved')
                : __('workflow.messages.validated'),
            DecisionWorkflow::Rejete => __('workflow.messages.rejected'),
            DecisionWorkflow::Retourne => __('workflow.messages.returned'),
            default => __('Décision enregistrée.'),
        };

        return redirect()->route('engagements.show', $engagement)->with('status', $statusMessage);
    }

    public function cancelApproved(CancelApprovedEngagementRequest $request, Engagement $engagement): RedirectResponse
    {
        app(EngagementCancellationService::class)->cancelApproved(
            $engagement,
            $request->validated('motif_annulation'),
            $request->user()
        );

        return redirect()
            ->route('engagements.show', $engagement->fresh())
            ->with('status', __('execution.messages.engagement_cancelled'));
    }

    protected function assertMontantWithinCredit(int $ligneId, string $montant, ?Engagement $current): void
    {
        $ligne = LigneBudgetaire::query()->findOrFail($ligneId);
        $disponible = $ligne->creditDisponible();

        if ($current !== null
            && $current->ligne_budgetaire_id === $ligneId
            && in_array($current->statut, [StatutEngagement::Brouillon, StatutEngagement::Retourne], true)
        ) {
            $disponible = bcadd($disponible, (string) $current->montant, 2);
        }

        if (bccomp($montant, $disponible, 2) > 0) {
            throw ValidationException::withMessages([
                'montant' => __('Le montant dépasse le crédit disponible sur la ligne (:dispo).', [
                    'dispo' => $disponible,
                ]),
            ]);
        }
    }

    protected function generateUniqueNumero(int $annee): string
    {
        return DB::transaction(function () use ($annee): string {
            do {
                $numero = sprintf('ENG-%d-%s', $annee, strtoupper(Str::random(8)));
            } while (Engagement::withTrashed()->where('numero', $numero)->exists());

            return $numero;
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function formDependencies(): array
    {
        return [
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'lignes' => LigneBudgetaire::query()
                ->with('exerciceBudgetaire')
                ->orderByDesc('exercice_budgetaire_id')
                ->orderBy('code')
                ->get(),
            'departements' => Departement::query()->orderBy('libelle')->get(),
            'directions' => Direction::query()->with('departement')->orderBy('libelle')->get(),
            'services' => Service::query()->with('direction.departement')->orderBy('libelle')->get(),
            'fournisseurs' => Fournisseur::query()->orderBy('raison_sociale')->get(),
            'agents' => Agent::query()->orderBy('nom')->orderBy('prenom')->get(),
        ];
    }
}
