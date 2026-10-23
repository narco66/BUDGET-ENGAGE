<x-app-layout>
    <x-slot name="header">
        <div class="page-header mb-0">
            <div>
                <nav class="page-breadcrumb mb-1">
                    <a href="{{ route('ged.documents.show', $signature->document) }}">{{ $signature->document->reference }}</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-ceeac-primary font-semibold">Vérification de signature</span>
                </nav>
                <h2 class="page-title">Vérification d'intégrité &amp; de signature</h2>
            </div>
            @if ($resultat['signe'])
                <a href="{{ route('ged.signatures.certificate', $signature) }}" class="btn btn-secondary btn-sm">Certificat PDF</a>
            @endif
        </div>
    </x-slot>

    @php($valide = $resultat['signe'] && $resultat['integrite_fichier'] && $resultat['scelle_valide'])

    <div class="max-w-3xl mx-auto space-y-4">
        <div class="{{ $valide ? 'alert-success' : 'alert-danger' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if ($valide)
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                @endif
            </svg>
            <span class="font-semibold">
                {{ $valide
                    ? 'Signature valide : le document est intègre et le scellé est authentique.'
                    : ($resultat['signe'] ? 'ALERTE : le document a été altéré ou le scellé est invalide.' : 'Document non signé.') }}
            </span>
        </div>

        <div class="card">
            <div class="card-body text-sm space-y-3">
                <div class="grid md:grid-cols-2 gap-3">
                    <div><span class="text-gray-500">Document</span><p class="font-medium">{{ $signature->document->titre }}</p></div>
                    <div><span class="text-gray-500">Référence</span><p class="font-mono text-xs">{{ $signature->document->reference }}</p></div>
                    <div><span class="text-gray-500">Signataire</span><p class="font-medium">{{ $signature->signataire?->name ?? '—' }}</p></div>
                    <div><span class="text-gray-500">Horodatage</span><p class="font-medium">{{ $signature->horodatage?->format('d/m/Y H:i:s') ?? '—' }}</p></div>
                    <div><span class="text-gray-500">Algorithme</span><p class="font-medium">{{ $signature->algorithme }}</p></div>
                    <div><span class="text-gray-500">Statut</span><p class="font-medium">{{ $signature->statut->libelle() }}</p></div>
                </div>

                <div class="border-t border-gray-100 pt-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Intégrité du fichier (SHA-256)</span>
                        <span class="badge {{ $resultat['integrite_fichier'] ? 'badge-success' : 'badge-danger' }}">
                            {{ $resultat['integrite_fichier'] ? 'Conforme' : 'Non conforme' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Validité du scellé (HMAC)</span>
                        <span class="badge {{ $resultat['scelle_valide'] ? 'badge-success' : 'badge-danger' }}">
                            {{ $resultat['scelle_valide'] ? 'Authentique' : 'Invalide' }}
                        </span>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-3">
                    <span class="text-gray-500">Empreinte signée</span>
                    <p class="font-mono text-[11px] break-all text-gray-700">{{ $signature->hash_signe ?? '—' }}</p>
                    <span class="text-gray-500 mt-2 block">Empreinte actuelle du fichier</span>
                    <p class="font-mono text-[11px] break-all text-gray-700">{{ $resultat['hash_actuel'] ?? '— (fichier absent)' }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
