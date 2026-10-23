<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-xl font-bold text-ceeac-primary">Réinitialisation du mot de passe</h2>
        <p class="mt-2 text-sm text-gray-600">
            Saisissez votre adresse email pour recevoir un lien de réinitialisation sécurisé.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email institutionnel')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="pt-2 space-y-3">
            <x-primary-button class="w-full justify-center py-3 rounded-institutional normal-case tracking-normal text-base font-semibold">
                Envoyer le lien
            </x-primary-button>
            <a href="{{ route('login') }}" class="btn btn-ghost w-full">Retour à la connexion</a>
        </div>
    </form>
</x-guest-layout>
