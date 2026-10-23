<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <a href="{{ route('ged.workflows.index') }}">Circuits</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">Création</span>
                </nav>
                <h2 class="page-title">Nouveau circuit de validation</h2>
            </div>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('ged.workflows.store') }}" class="space-y-6">
                    @csrf
                    @include('ged.workflows._form', ['types' => $types])
                    <div class="flex items-center gap-3">
                        <button class="btn btn-primary">{{ __('Enregistrer') }}</button>
                        <a href="{{ route('ged.workflows.index') }}" class="btn btn-ghost">{{ __('Annuler') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
