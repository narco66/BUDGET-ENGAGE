<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\ConnexionLog;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.index', [
            'users' => User::query()
                ->with(['service', 'roles'])
                ->orderByDesc('created_at')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', [
            'services' => Service::query()->orderBy('libelle')->get(),
            'roles' => Role::query()->where('guard_name', 'web')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validated();

        $user = User::query()->create([
            'service_id' => $validated['service_id'] ?? null,
            'name' => $validated['name'],
            'prenom' => $validated['prenom'] ?? null,
            'email' => Str::lower($validated['email']),
            'telephone' => $validated['telephone'] ?? null,
            'password' => $validated['password'],
            'statut' => 'actif',
        ]);

        $user->assignRole($validated['role']);
        $user->sendEmailVerificationNotification();

        return redirect()
            ->route('dashboard')
            ->with('status', __('Utilisateur créé. Un e-mail de vérification a été envoyé.'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', [
            'userModel' => $user->load('roles'),
            'services' => Service::query()->orderBy('libelle')->get(),
            'roles' => Role::query()->where('guard_name', 'web')->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, User $user): View
    {
        $this->authorize('view', $user);

        $user->load(['service', 'roles']);
        $period = (int) $request->integer('period', 30);
        if (! in_array($period, [7, 30, 90], true)) {
            $period = 30;
        }
        $from = now()->subDays($period);

        $budgetActivities = Activity::query()
            ->where('log_name', 'budget')
            ->where('causer_type', User::class)
            ->where('causer_id', $user->id)
            ->where('created_at', '>=', $from)
            ->latest('created_at')
            ->take(80)
            ->get()
            ->map(function (Activity $activity): array {
                return [
                    'type' => 'budget',
                    'when' => $activity->created_at,
                    'title' => $activity->description,
                    'meta' => $activity->properties?->toArray() ?? [],
                ];
            });

        $connections = ConnexionLog::query()
            ->where('user_id', $user->id)
            ->where('connecte_le', '>=', $from)
            ->latest('connecte_le')
            ->take(80)
            ->get()
            ->map(fn (ConnexionLog $log): array => [
                'type' => 'connexion',
                'when' => $log->connecte_le,
                'title' => $log->succes ? 'Connexion réussie' : 'Tentative échouée',
                'meta' => [
                    'adresse_ip' => $log->adresse_ip,
                    'user_agent' => $log->user_agent,
                    'email' => $log->email,
                ],
            ]);

        $timeline = $budgetActivities
            ->concat($connections)
            ->sortByDesc(fn (array $item) => $item['when']?->timestamp ?? 0)
            ->values();

        $perPage = 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $items = $timeline->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $events = new LengthAwarePaginator(
            $items,
            $timeline->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.users.show', [
            'userModel' => $user,
            'events' => $events,
            'period' => $period,
        ]);
    }

    public function exportTimelineCsv(Request $request, User $user): StreamedResponse
    {
        $this->authorize('view', $user);

        $period = (int) $request->integer('period', 30);
        if (! in_array($period, [7, 30, 90], true)) {
            $period = 30;
        }
        $from = now()->subDays($period);

        $budgetActivities = Activity::query()
            ->where('log_name', 'budget')
            ->where('causer_type', User::class)
            ->where('causer_id', $user->id)
            ->where('created_at', '>=', $from)
            ->latest('created_at')
            ->get()
            ->map(function (Activity $activity): array {
                return [
                    'type' => 'journal_budget',
                    'date_heure' => optional($activity->created_at)->format('Y-m-d H:i:s'),
                    'titre' => $activity->description,
                    'meta' => json_encode($activity->properties?->toArray() ?? [], JSON_UNESCAPED_UNICODE),
                ];
            });

        $connections = ConnexionLog::query()
            ->where('user_id', $user->id)
            ->where('connecte_le', '>=', $from)
            ->latest('connecte_le')
            ->get()
            ->map(fn (ConnexionLog $log): array => [
                'type' => 'connexion',
                'date_heure' => optional($log->connecte_le)->format('Y-m-d H:i:s'),
                'titre' => $log->succes ? 'Connexion réussie' : 'Tentative échouée',
                'meta' => json_encode([
                    'adresse_ip' => $log->adresse_ip,
                    'user_agent' => $log->user_agent,
                    'email' => $log->email,
                ], JSON_UNESCAPED_UNICODE),
            ]);

        $timeline = $budgetActivities->concat($connections)->sortByDesc('date_heure')->values();
        $filename = 'timeline_utilisateur_'.$user->id.'_'.$period.'j.csv';

        return response()->streamDownload(function () use ($timeline): void {
            $out = fopen('php://output', 'wb');
            fputcsv($out, ['type', 'date_heure', 'titre', 'meta']);
            foreach ($timeline as $row) {
                fputcsv($out, [$row['type'], $row['date_heure'], $row['titre'], $row['meta']]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validated();
        $payload = [
            'service_id' => $validated['service_id'] ?? null,
            'name' => $validated['name'],
            'prenom' => $validated['prenom'] ?? null,
            'email' => Str::lower($validated['email']),
            'telephone' => $validated['telephone'] ?? null,
            'statut' => $validated['statut'],
        ];

        if (filled($validated['password'] ?? null)) {
            $payload['password'] = $validated['password'];
        }

        $user->update($payload);
        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('Utilisateur mis à jour.'));
    }
}
