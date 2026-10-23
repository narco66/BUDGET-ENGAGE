<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <span>Administration</span><span class="text-gray-300">/</span><span class="text-ceeac-primary font-semibold">Utilisateurs</span>
                </nav>
                <h2 class="page-title">Gestion des utilisateurs</h2>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">Nouvel utilisateur</a>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="data-table min-w-full text-sm">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Service</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->name }} {{ $user->prenom }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</td>
                                <td>{{ $user->service?->libelle ?? '—' }}</td>
                                <td><span class="badge {{ $user->statut === 'actif' ? 'badge-success' : 'badge-neutral' }}">{{ $user->statut }}</span></td>
                                <td class="text-right">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost btn-sm">Détail</a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn-sm">Modifier</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $users->links() }}</div>
        </div>
    </div>
</x-app-layout>
