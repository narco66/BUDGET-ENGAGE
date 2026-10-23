<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <span>Administration</span><span class="text-gray-300">/</span><span class="text-ceeac-primary font-semibold">Rôles</span>
                </nav>
                <h2 class="page-title">Gestion des rôles</h2>
            </div>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="data-table min-w-full text-sm">
                    <thead>
                        <tr>
                            <th>Rôle</th>
                            <th>Permissions</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                            <tr>
                                <td class="font-medium">{{ $role->name }}</td>
                                <td>{{ $role->permissions->pluck('name')->join(', ') }}</td>
                                <td class="text-right"><a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-secondary btn-sm">Modifier</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
