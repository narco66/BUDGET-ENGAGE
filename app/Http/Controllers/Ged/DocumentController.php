<?php

namespace App\Http\Controllers\Ged;

use App\Enums\NiveauConfidentialiteGed;
use App\Enums\StatutDocumentGed;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ged\StoreGedDocumentRequest;
use App\Http\Requests\Ged\UpdateGedDocumentRequest;
use App\Models\ExerciceBudgetaire;
use App\Models\GedCategorie;
use App\Models\GedDocument;
use App\Models\GedDossier;
use App\Models\GedTypeDocument;
use App\Services\Ged\GedDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    public function __construct(private readonly GedDocumentService $service)
    {
        $this->authorizeResource(GedDocument::class, 'document');
    }

    public function index(Request $request): View
    {
        $query = GedDocument::query()
            ->with(['typeDocument', 'categorie', 'dossier', 'createur'])
            ->orderByDesc('created_at');

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($q): void {
                $builder->where('titre', 'like', "%{$q}%")
                    ->orWhere('reference', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->input('statut'));
        }

        if ($request->filled('type_document_id')) {
            $query->where('type_document_id', $request->integer('type_document_id'));
        }

        if ($request->filled('niveau_confidentialite')) {
            $query->where('niveau_confidentialite', $request->input('niveau_confidentialite'));
        }

        // Cloisonnement confidentialité (source unique : scope GedDocument::visiblePour).
        $query->visiblePour($request->user());

        $documents = $query->paginate(20)->appends($request->query());
        $types = GedTypeDocument::query()->where('statut', 'actif')->orderBy('libelle')->get();

        return view('ged.documents.index', compact('documents', 'types'));
    }

    public function create(): View
    {
        return view('ged.documents.create', $this->formOptions());
    }

    public function store(StoreGedDocumentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $file = $request->file('fichier');
        unset($validated['fichier']);

        $document = $this->service->store($validated, $file, $request->user());

        return redirect()
            ->route('ged.documents.show', $document)
            ->with('success', __('Document déposé sous la référence :ref.', ['ref' => $document->reference]));
    }

    public function show(GedDocument $document, \App\Services\Ged\GedWorkflowService $workflow): View
    {
        $document->load([
            'typeDocument', 'categorie', 'dossier', 'conservationRule', 'workflow',
            'createur', 'modificateur', 'versions.createur',
            'validations.validateur', 'signatures.signataire',
            'partages' => fn ($q) => $q->where('est_actif', true)->with('partageAvec'),
            'attachments.attachable',
            'journaux' => fn ($q) => $q->with('utilisateur')->latest('created_at')->limit(20),
        ]);

        $fichierPresent = $this->service->fileExists($document);
        $etapeCourante = $workflow->currentPendingStep($document);
        $peutDecider = $etapeCourante !== null && $workflow->userCanValidate(request()->user(), $etapeCourante);
        $signataires = \App\Models\User::query()->permission('ged.sign')->orderBy('name')->get();
        $utilisateurs = \App\Models\User::query()->permission('ged.view')->orderBy('name')->get();
        $typesRattachables = array_keys(\App\Http\Controllers\Ged\DocumentAttachmentController::TYPES);

        return view('ged.documents.show', compact(
            'document', 'fichierPresent', 'etapeCourante', 'peutDecider',
            'signataires', 'utilisateurs', 'typesRattachables'
        ));
    }

    public function edit(GedDocument $document): View
    {
        return view('ged.documents.edit', array_merge(['document' => $document], $this->formOptions()));
    }

    public function update(UpdateGedDocumentRequest $request, GedDocument $document): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $document->update($data);

        $this->service->journal($document, $request->user(), 'modification', null, [
            'titre' => $document->titre,
        ]);

        return redirect()
            ->route('ged.documents.show', $document)
            ->with('success', __('Document mis à jour.'));
    }

    public function destroy(GedDocument $document): RedirectResponse
    {
        $user = request()->user();

        $document->forceFill([
            'statut' => StatutDocumentGed::Supprime->value,
            'deleted_by' => $user->id,
        ])->save();

        \App\Models\GedCorbeille::query()->create([
            'ged_document_id' => $document->id,
            'supprime_par' => $user->id,
            'date_suppression' => now(),
        ]);

        $this->service->journal($document, $user, 'suppression_logique');

        $document->delete();

        return redirect()
            ->route('ged.documents.index')
            ->with('success', __('Document placé dans la corbeille.'));
    }

    public function download(GedDocument $document): BinaryFileResponse
    {
        $this->authorize('download', $document);

        abort_unless($this->service->fileExists($document), 404);

        $this->service->journal($document, request()->user(), 'telechargement');

        return Response::download(
            $this->service->absolutePath($document->chemin_fichier),
            $document->nom_original ?? ($document->reference.'.'.$document->extension)
        );
    }

    public function storeVersion(Request $request, GedDocument $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $request->validate([
            'fichier' => [
                'required', 'file', 'max:51200',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg,gif,txt,csv,zip',
            ],
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->service->addVersion(
            $document,
            $request->file('fichier'),
            $request->input('commentaire'),
            $request->user()
        );

        return redirect()
            ->route('ged.documents.show', $document)
            ->with('success', __('Nouvelle version enregistrée.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(): array
    {
        return [
            'categories' => GedCategorie::query()->where('statut', 'actif')->orderBy('libelle')->get(),
            'types' => GedTypeDocument::query()->where('statut', 'actif')->orderBy('libelle')->get(),
            'dossiers' => GedDossier::query()->where('statut', 'actif')->orderBy('libelle')->get(),
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'niveaux' => NiveauConfidentialiteGed::cases(),
        ];
    }
}
