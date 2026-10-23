@if (session('status'))
    <x-ds.alert type="success" class="mb-4 animate-slide-up">{{ session('status') }}</x-ds.alert>
@endif
@if (session('success'))
    <x-ds.alert type="success" class="mb-4 animate-slide-up">{{ session('success') }}</x-ds.alert>
@endif
@if (session('error'))
    <x-ds.alert type="danger" class="mb-4 animate-slide-up">{{ session('error') }}</x-ds.alert>
@endif
@if (session('warning'))
    <x-ds.alert type="warning" class="mb-4 animate-slide-up">{{ session('warning') }}</x-ds.alert>
@endif
