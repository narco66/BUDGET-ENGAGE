<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
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
}
