<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <a href="{{ route('admin.users.index') }}" class="hover:text-ceeac-primary">Utilisateurs</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">{{ $userModel->name }} {{ $userModel->prenom }}</span>
                </nav>
                <h2 class="page-title">Détail utilisateur</h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.users.edit', $userModel) }}" class="btn btn-secondary btn-sm">Modifier</a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">Retour</a>
            </div>
        </div>
    </x-slot>

    <div class="grid lg:grid-cols-3 gap-4">
        <div class="card">
            <div class="card-body text-sm space-y-2">
                <h3 class="font-semibold text-gray-900">Profil</h3>
                <div><span class="text-gray-500">Nom</span><p class="font-medium">{{ $userModel->name }} {{ $userModel->prenom }}</p></div>
                <div><span class="text-gray-500">Email</span><p class="font-medium">{{ $userModel->email }}</p></div>
                <div><span class="text-gray-500">Téléphone</span><p class="font-medium">{{ $userModel->telephone ?: '—' }}</p></div>
                <div><span class="text-gray-500">Service</span><p class="font-medium">{{ $userModel->service?->libelle ?? '—' }}</p></div>
                <div><span class="text-gray-500">Rôles</span><p class="font-medium">{{ $userModel->roles->pluck('name')->join(', ') ?: '—' }}</p></div>
                <div><span class="text-gray-500">Statut</span><p><span class="badge {{ $userModel->statut === 'actif' ? 'badge-success' : 'badge-neutral' }}">{{ $userModel->statut }}</span></p></div>
            </div>
        </div>

        <div class="card lg:col-span-2">
            <div class="card-header"><h3 class="font-semibold text-gray-900">Historique d’actions (RH / audit)</h3></div>
            <div class="card-body">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex gap-2">
                        @foreach([7, 30, 90] as $p)
                            <a href="{{ route('admin.users.show', ['user' => $userModel, 'period' => $p]) }}"
                               class="btn btn-sm {{ (int) $period === $p ? 'btn-primary' : 'btn-ghost' }}">{{ $p }}j</a>
                        @endforeach
                    </div>
                    <a href="{{ route('admin.users.timeline.export.csv', ['user' => $userModel, 'period' => $period]) }}" class="btn btn-secondary btn-sm">
                        Export CSV
                    </a>
                </div>
                @if($events->isEmpty())
                    <p class="text-sm text-gray-500">Aucun événement disponible pour cet utilisateur.</p>
                @else
                    <div class="space-y-3">
                        @foreach($events as $event)
                            <div class="border border-gray-100 rounded-xl p-3 text-sm">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="badge {{ $event['type'] === 'budget' ? 'badge-info' : 'badge-neutral' }}">{{ $event['type'] === 'budget' ? 'Journal budget' : 'Connexion' }}</span>
                                        <span class="font-medium text-gray-900">{{ $event['title'] }}</span>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ optional($event['when'])->format('d/m/Y H:i') }}</span>
                                </div>
                                @if(!empty($event['meta']))
                                    <div class="mt-2 text-xs text-gray-600 space-y-1">
                                        @foreach($event['meta'] as $key => $value)
                                            <p><span class="font-medium">{{ $key }}:</span> {{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE) }}</p>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">{{ $events->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
