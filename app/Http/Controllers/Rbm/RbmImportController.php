<?php

namespace App\Http\Controllers\Rbm;

use App\Exports\RbmImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rbm\StoreRbmImportRequest;
use App\Services\Rbm\RbmImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RbmImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:rbm.import|rbm.admin');
    }

    public function index(): View
    {
        return view('rbm.import.index');
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new RbmImportTemplateExport, 'modele-import-rbm-gar.xlsx');
    }

    public function store(StoreRbmImportRequest $request, RbmImportService $importService): RedirectResponse|View
    {
        $rawRows = Excel::toArray([], $request->file('fichier'))[0] ?? [];
        $parsed = $importService->parseAndValidate($rawRows);

        if ($parsed['errors'] !== []) {
            return view('rbm.import.result', [
                'success' => false,
                'imported' => 0,
                'errors' => $parsed['errors'],
                'previewCount' => count($parsed['rows']),
            ]);
        }

        $result = $importService->import($parsed['rows'], $request->user());

        return view('rbm.import.result', [
            'success' => true,
            'imported' => $result['imported'],
            'errors' => [],
            'previewCount' => $result['imported'],
        ]);
    }
}
