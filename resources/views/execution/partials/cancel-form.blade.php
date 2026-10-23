@if ($cancelRoute ?? false)
    <form method="POST" action="{{ $cancelRoute }}" class="border border-red-100 bg-red-50/40 rounded-xl p-3 space-y-2"
          onsubmit="return confirm(@json(__('Confirmer l\'annulation (régularisation) ?')));">
        @csrf
        <label class="form-label text-red-800">{{ __('execution.actions.cancel') }}</label>
        <div class="flex flex-wrap gap-2 items-end">
            <input type="text" name="motif_annulation" class="form-input flex-1" placeholder="Motif (optionnel)" maxlength="1000" />
            <button class="btn btn-secondary btn-sm text-red-700">{{ __('execution.actions.cancel') }}</button>
        </div>
    </form>
@endif
