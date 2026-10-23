<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <a href="{{ route('ged.conservation-rules.index') }}">Règles de conservation</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">{{ $rule->libelle }}</span>
                </nav>
                <h2 class="page-title">Modifier la règle</h2>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('ged.conservation-rules.update', $rule) }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    @include('ged.conservation.rules._form', ['rule' => $rule, 'types' => $types, 'sortsFinaux' => $sortsFinaux])
                    <div class="flex items-center gap-3">
                        <button class="btn btn-primary">{{ __('Enregistrer') }}</button>
                        <a href="{{ route('ged.conservation-rules.index') }}" class="btn btn-ghost">{{ __('Annuler') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
