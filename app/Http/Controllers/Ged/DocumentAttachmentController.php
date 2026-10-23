<?php

namespace App\Http\Controllers\Ged;

use App\Http\Controllers\Controller;
use App\Models\Engagement;
use App\Models\ExerciceBudgetaire;
use App\Models\GedDocument;
use App\Models\GedDocumentAttachment;
use App\Models\LigneBudgetaire;
use App\Models\Liquidation;
use App\Models\Ordonnancement;
use App\Models\Paiement;
use App\Services\Ged\GedDocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentAttachmentController extends Controller
{
    /**
     * Modules métier rattachables (clé courte → modèle Eloquent).
     *
     * @var array<string, class-string>
     */
    public const TYPES = [
        'engagement' => Engagement::class,
        'liquidation' => Liquidation::class,
        'ordonnancement' => Ordonnancement::class,
        'paiement' => Paiement::class,
        'ligne_budgetaire' => LigneBudgetaire::class,
        'exercice_budgetaire' => ExerciceBudgetaire::class,
    ];

    public function __construct(private readonly GedDocumentService $documents)
    {
    }

    public function store(Request $request, GedDocument $document): RedirectResponse
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'attachable_type' => ['required', Rule::in(array_keys(self::TYPES))],
            'attachable_id' => ['required', 'integer', 'min:1'],
            'role' => ['nullable', 'string', 'max:100'],
        ]);

        /** @var class-string $modelClass */
        $modelClass = self::TYPES[$validated['attachable_type']];

        // Vérifie l'existence de l'entité métier cible.
        abort_unless($modelClass::query()->whereKey($validated['attachable_id'])->exists(), 422, __('Entité métier introuvable.'));

        GedDocumentAttachment::query()->firstOrCreate(
            [
                'ged_document_id' => $document->id,
                'attachable_type' => $modelClass,
                'attachable_id' => $validated['attachable_id'],
            ],
            [
                'role' => $validated['role'] ?? null,
                'created_by' => $request->user()->id,
            ]
        );

        $this->documents->journal($document, $request->user(), 'rattachement', null, [
            'attachable_type' => $validated['attachable_type'],
            'attachable_id' => $validated['attachable_id'],
        ]);

        return redirect()
            ->route('ged.documents.show', $document)
            ->with('success', __('Document rattaché à :type #:id.', [
                'type' => $validated['attachable_type'],
                'id' => $validated['attachable_id'],
            ]));
    }

    public function destroy(GedDocumentAttachment $attachment): RedirectResponse
    {
        $attachment->loadMissing('document');
        $this->authorize('update', $attachment->document);

        $document = $attachment->document;
        $attachment->delete();

        $this->documents->journal($document, request()->user(), 'detachement');

        return redirect()
            ->route('ged.documents.show', $document)
            ->with('success', __('Rattachement supprimé.'));
    }
}
