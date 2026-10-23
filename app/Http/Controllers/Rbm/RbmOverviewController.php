<?php

namespace App\Http\Controllers\Rbm;

use App\Http\Controllers\Controller;
use App\Services\Rbm\RbmTreeService;
use Illuminate\View\View;

class RbmOverviewController extends Controller
{
    public function __construct(
        protected RbmTreeService $treeService
    ) {
        $this->middleware('permission:rbm.view|rbm.admin|rbm.create|rbm.update');
    }

    public function tree(): View
    {
        $piliers = $this->treeService->fullTree();

        return view('rbm.overview.tree', compact('piliers'));
    }

    public function matrix(): View
    {
        $data = $this->treeService->matrixData();

        return view('rbm.overview.matrix', [
            'rows' => $data['rows'],
            'piliers' => $data['piliers'],
        ]);
    }

    public function synthesis(): View
    {
        $piliers = $this->treeService->fullTree();

        return view('rbm.overview.synthesis', compact('piliers'));
    }
}
