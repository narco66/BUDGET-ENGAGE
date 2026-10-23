<?php

namespace App\Http\Controllers\Engagements;

use App\Http\Controllers\Controller;
use App\Models\Engagement;
use App\Services\Engagement\EngagementGedService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EngagementGedController extends Controller
{
    public function __construct(private readonly EngagementGedService $ged)
    {
    }

    public function store(Request $request, Engagement $engagement): RedirectResponse
    {
        $this->authorize('update', $engagement);

        $validated = $request->validate([
            'fichier' => [
                'required', 'file', 'max:51200',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,png,jpg,jpeg,gif,txt,csv,zip',
            ],
            'titre' => ['nullable', 'string', 'max:255'],
        ]);

        $this->ged->deposer($engagement, $request->file('fichier'), $request->user(), $validated['titre'] ?? null);

        return redirect()
            ->route('engagements.show', $engagement)
            ->with('status', __('Pièce déposée dans la GED et rattachée à l’engagement.'));
    }
}
