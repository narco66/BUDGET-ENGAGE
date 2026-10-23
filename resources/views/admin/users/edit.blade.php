<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Modifier utilisateur</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.users.update', $userModel) }}" class="space-y-4">
                    @csrf @method('PATCH')
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="name" :value="'Nom'" />
                            <x-text-input id="name" name="name" type="text" class="form-input mt-1" :value="old('name', $userModel->name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>
                        <div>
                            <x-input-label for="prenom" :value="'Prénom'" />
                            <x-text-input id="prenom" name="prenom" type="text" class="form-input mt-1" :value="old('prenom', $userModel->prenom)" />
                            <x-input-error class="mt-2" :messages="$errors->get('prenom')" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="email" :value="'Adresse e-mail'" />
                            <x-text-input id="email" name="email" type="email" class="form-input mt-1" :value="old('email', $userModel->email)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>
                        <div>
                            <x-input-label for="telephone" :value="'Téléphone'" />
                            <x-text-input id="telephone" name="telephone" type="text" class="form-input mt-1" :value="old('telephone', $userModel->telephone)" />
                            <x-input-error class="mt-2" :messages="$errors->get('telephone')" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="service_id" :value="'Service'" />
                            <select id="service_id" name="service_id" class="form-select mt-1">
                                <option value="">— Aucun —</option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}" @selected((string) old('service_id', $userModel->service_id) === (string) $service->id)>{{ $service->libelle }} ({{ $service->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="role" :value="'Rôle'" />
                            <select id="role" name="role" class="form-select mt-1" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}" @selected(old('role', $userModel->roles->first()?->name) === $role->name)>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="statut" :value="'Statut'" />
                            <select id="statut" name="statut" class="form-select mt-1" required>
                                <option value="actif" @selected(old('statut', $userModel->statut) === 'actif')>actif</option>
                                <option value="inactif" @selected(old('statut', $userModel->statut) === 'inactif')>inactif</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="password" :value="'Nouveau mot de passe (optionnel)'" />
                            <x-text-input id="password" name="password" type="password" class="form-input mt-1" />
                            <x-input-error class="mt-2" :messages="$errors->get('password')" />
                        </div>
                        <div>
                            <x-input-label for="password_confirmation" :value="'Confirmation'" />
                            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="form-input mt-1" />
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <button class="btn btn-primary">Enregistrer</button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Retour</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
