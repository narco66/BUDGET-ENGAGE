@include('rbm.partials.referential-form', ['item' => $item ?? null])
<div>
    <x-input-label for="annee" :value="__('Année')" />
    <x-text-input id="annee" name="annee" type="number" class="block mt-1 w-full" :value="old('annee', $item?->annee)" />
</div>
<div>
    <x-input-label for="version" :value="__('Version')" />
    <x-text-input id="version" name="version" type="text" class="block mt-1 w-full" :value="old('version', $item?->version ?? '1.0')" />
</div>