<?php

namespace App\Http\Controllers\Ged;

use App\Enums\NiveauConfidentialiteGed;
use App\Http\Controllers\Controller;
use App\Models\GedDocument;
use App\Policies\GedDocumentPolicy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpecialListController extends Controller
{
    public function confidentiels(Request $request): View
    {
        abort_unless($request->user()?->can('ged.view'), 403);

        $query = GedDocument::query()
            ->with(['typeDocument', 'createur'])
            ->whereIn('niveau_confidentialite', [
                NiveauConfidentialiteGed::Confidentiel->value,
                NiveauConfidentialiteGed::TresConfidentiel->value,
            ])
            ->orderByDesc('created_at');

        // Les documents très sensibles ne sont listés qu'à l'administrateur GED
        // ou au propriétaire.
        if (! GedDocumentPolicy::seesAllDocuments($request->user())) {
            $query->where('created_by', $request->user()->id);
        }

        $documents = $query->paginate(20);

        return view('ged.listings.index', [
            'titre' => 'Documents confidentiels',
            'sous_titre' => 'Documents classés Confidentiel ou Très confidentiel.',
            'documents' => $documents,
        ]);
    }

    public function expires(Request $request): View
    {
        abort_unless($request->user()?->can('ged.view'), 403);

        $documents = GedDocument::query()
            ->visiblePour($request->user())
            ->with(['typeDocument', 'createur'])
            ->whereNotNull('date_expiration')
            ->whereDate('date_expiration', '<=', now())
            ->orderBy('date_expiration')
            ->paginate(20);

        return view('ged.listings.index', [
            'titre' => 'Documents expirés',
            'sous_titre' => "Documents dont la date d'expiration est dépassée.",
            'documents' => $documents,
        ]);
    }
}
