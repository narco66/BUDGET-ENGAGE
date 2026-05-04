<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class BudgetActivityJournalController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()->can('audit.view')
                || $request->user()->can('reports.view')
                || $request->user()->can('exercises.manage'),
            403
        );

        $activities = Activity::query()
            ->where('log_name', 'budget')
            ->latest()
            ->with(['causer', 'subject'])
            ->paginate(40)
            ->withQueryString();

        return view('reports.activity.index', compact('activities'));
    }
}
