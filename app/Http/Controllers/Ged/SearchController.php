<?php

namespace App\Http\Controllers\Ged;

use App\Enums\NiveauConfidentialiteGed;
use App\Enums\StatutDocumentGed;
use App\Http\Controllers\Controller;
use App\Models\ExerciceBudgetaire;
use App\Models\GedCategorie;
use App\Models\GedDossier;
use App\Models\GedTag;
use App\Models\GedTypeDocument;
use App\Support\GedDocumentSearch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('ged.search'), 403);

        $documents = null;
        if (GedDocumentSearch::hasActiveFilters($request)) {
            $documents = GedDocumentSearch::query($request, $request->user())
                ->paginate(20)
                ->appends($request->query());
        }

        return view('ged.search.index', array_merge(
            ['documents' => $documents],
            $this->options()
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function options(): array
    {
        return [
            'types' => GedTypeDocument::query()->orderBy('libelle')->get(),
            'categories' => GedCategorie::query()->orderBy('libelle')->get(),
            'dossiers' => GedDossier::query()->orderBy('libelle')->get(),
            'exercices' => ExerciceBudgetaire::query()->orderByDesc('annee')->get(),
            'tags' => GedTag::query()->orderBy('libelle')->get(),
            'statuts' => StatutDocumentGed::cases(),
            'niveaux' => NiveauConfidentialiteGed::cases(),
        ];
    }
}
