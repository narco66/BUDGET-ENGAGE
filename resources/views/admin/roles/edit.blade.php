<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Modifier rôle: {{ $roleModel->name }}</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.roles.update', $roleModel) }}" class="space-y-4">
                    @csrf @method('PATCH')
                    <div class="grid md:grid-cols-2 gap-3">
                        @php($selected = collect(old('permissions', $roleModel->permissions->pluck('name')->all())))
                        @foreach($permissions as $permission)
                            <label class="flex items-center gap-2 text-sm border border-gray-200 rounded-lg p-2">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked($selected->contains($permission->name)) />
                                <span>{{ $permission->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('permissions')" />
                    <x-input-error class="mt-2" :messages="$errors->get('permissions.*')" />
                    <div class="flex items-center gap-3">
                        <button class="btn btn-primary">Enregistrer</button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-ghost">Retour</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
