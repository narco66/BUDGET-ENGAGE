<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
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

        $query = Activity::query()
            ->where('log_name', 'budget')
            ->with(['causer', 'subject']);

        if ($request->filled('q')) {
            $q = (string) $request->input('q');
            $query->where(function ($builder) use ($q): void {
                $builder->where('description', 'like', "%{$q}%")
                    ->orWhere('subject_type', 'like', "%{$q}%");
            });
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->integer('causer_id'));
        }

        $activities = $query->latest()->paginate(40)->withQueryString();

        return view('reports.activity.index', compact('activities'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        abort_unless(
            $request->user()->can('audit.view')
                || $request->user()->can('reports.view')
                || $request->user()->can('exercises.manage'),
            403
        );

        $query = Activity::query()->where('log_name', 'budget')->with(['causer']);
        if ($request->filled('q')) {
            $q = (string) $request->input('q');
            $query->where('description', 'like', "%{$q}%");
        }

        $activities = $query->latest()->limit(5000)->get();

        return response()->stream(function () use ($activities): void {
            $handle = fopen('php://output', 'wb');
            if ($handle === false) {
                return;
            }
            fputcsv($handle, ['Date', 'Utilisateur', 'Description', 'Sujet', 'ID sujet']);
            foreach ($activities as $activity) {
                fputcsv($handle, [
                    $activity->created_at?->format('Y-m-d H:i:s'),
                    $activity->causer?->name,
                    $activity->description,
                    $activity->subject_type ? class_basename((string) $activity->subject_type) : '',
                    $activity->subject_id,
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="journal-activite-budget.csv"',
        ]);
    }
}
