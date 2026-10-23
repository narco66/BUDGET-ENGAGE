<?php

namespace App\Support;

use App\Models\GedDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Construit la requête de recherche documentaire à partir des filtres de la
 * requête HTTP, en appliquant systématiquement le cloisonnement par niveau de
 * confidentialité. Réutilisé par la recherche avancée et les exports afin de
 * garantir une cohérence totale des résultats.
 */
class GedDocumentSearch
{
    /**
     * @return Builder<GedDocument>
     */
    public static function query(Request $request, User $user): Builder
    {
        $query = GedDocument::query()
            ->with(['typeDocument', 'categorie', 'dossier', 'exerciceBudgetaire', 'createur'])
            ->orderByDesc('created_at');

        // Recherche plein-texte simple (titre / référence / description / OCR).
        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function (Builder $b) use ($q): void {
                $b->where('titre', 'like', "%{$q}%")
                    ->orWhere('reference', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('ocr_contenu', 'like', "%{$q}%");
            });
        }

        $exacts = [
            'reference' => 'reference',
            'statut' => 'statut',
            'niveau_confidentialite' => 'niveau_confidentialite',
            'type_document_id' => 'type_document_id',
            'categorie_id' => 'categorie_id',
            'dossier_id' => 'dossier_id',
            'exercice_budgetaire_id' => 'exercice_budgetaire_id',
            'module_source' => 'module_source',
            'module_source_id' => 'module_source_id',
            'created_by' => 'created_by',
        ];

        foreach ($exacts as $input => $column) {
            if ($request->filled($input)) {
                if ($input === 'reference') {
                    $query->where($column, 'like', '%'.trim((string) $request->input($input)).'%');
                } else {
                    $query->where($column, $request->input($input));
                }
            }
        }

        // Filtres booléens.
        if ($request->filled('est_archive')) {
            $query->where('est_archive', $request->boolean('est_archive'));
        }
        if ($request->filled('est_signe')) {
            $query->where('est_signe', $request->boolean('est_signe'));
        }

        // Plages de dates.
        $dateRanges = ['date_document', 'date_depot', 'date_expiration', 'date_archivage'];
        foreach ($dateRanges as $field) {
            if ($request->filled($field.'_from')) {
                $query->whereDate($field, '>=', $request->input($field.'_from'));
            }
            if ($request->filled($field.'_to')) {
                $query->whereDate($field, '<=', $request->input($field.'_to'));
            }
        }

        // Tag (par identifiant).
        if ($request->filled('tag_id')) {
            $query->whereHas('tags', fn (Builder $b) => $b->where('ged_tags.id', $request->integer('tag_id')));
        }

        // Cloisonnement confidentialité (source unique : scope GedDocument::visiblePour).
        $query->visiblePour($user);

        return $query;
    }

    public static function hasActiveFilters(Request $request): bool
    {
        return collect($request->query())
            ->except(['page'])
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->isNotEmpty();
    }
}
