<?php

namespace App\Http\Controllers\Rbm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rbm\Concerns\AppliesRbmListingFilters;
use App\Http\Requests\Rbm\StoreSourceVerificationRequest;
use App\Http\Requests\Rbm\UpdateSourceVerificationRequest;
use App\Models\SourceVerification;
use App\Services\Rbm\RbmReferentialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SourceVerificationController extends Controller
{
    use AppliesRbmListingFilters;

    public function __construct(protected RbmReferentialService $rbmService)
    {
        $this->authorizeResource(SourceVerification::class, 'source_verification');
    }

    public function index(Request $request): View
    {
        $sources_verification = $this->applyListingFilters(
            SourceVerification::query()->withCount('indicateurs'),
            $request
        )->paginate(15)->withQueryString();

        return view('rbm.sources-verification.index', ['items' => $sources_verification]);
    }

    public function create(): View
    {
        return view('rbm.sources-verification.create');
    }

    public function store(StoreSourceVerificationRequest $request): RedirectResponse
    {
        $this->rbmService->create(SourceVerification::class, $request->validated(), $request->user());

        return redirect()->route('rbm.sources-verification.index')->with('status', __('Enregistrement créé.'));
    }

    public function show(SourceVerification $source_verification): View
    {
        $source_verification->loadCount('indicateurs');

        return view('rbm.sources-verification.show', compact('source_verification'));
    }

    public function edit(SourceVerification $source_verification): View
    {
        return view('rbm.sources-verification.edit', compact('source_verification'));
    }

    public function update(UpdateSourceVerificationRequest $request, SourceVerification $source_verification): RedirectResponse
    {
        $this->rbmService->update($source_verification, $request->validated(), $request->user());

        return redirect()->route('rbm.sources-verification.index')->with('status', __('Enregistrement mis à jour.'));
    }

    public function destroy(SourceVerification $source_verification): RedirectResponse
    {
        $this->rbmService->archive($source_verification, request()->user());

        return redirect()->route('rbm.sources-verification.index')->with('status', __('Enregistrement archivé.'));
    }
}
