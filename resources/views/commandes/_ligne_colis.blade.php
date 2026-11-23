<div class="ligne-colis rounded-lg border border-slate-200 p-4">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Description</label>
            <input type="text" name="colis[{{ $i }}][description]" value="{{ $ligne['description'] ?? '' }}"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Poids (kg)</label>
            <input type="number" step="0.01" min="0.01" max="10" name="colis[{{ $i }}][poids]" value="{{ $ligne['poids'] ?? '' }}"
                   class="w-full rounded-lg border px-3 py-2 text-sm {{ $errors->has("colis.$i.poids") ? 'border-red-400' : 'border-slate-300' }}">
            @error("colis.$i.poids") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Dimensions</label>
            <input type="text" name="colis[{{ $i }}][dimensions]" value="{{ $ligne['dimensions'] ?? '' }}" placeholder="30 x 20 x 10 cm"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Valeur déclarée (Ar)</label>
            <input type="number" min="0" name="colis[{{ $i }}][valeur_declaree]" value="{{ $ligne['valeur_declaree'] ?? '' }}"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
    </div>
    <div class="mt-3 flex items-center justify-between gap-3">
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="colis[{{ $i }}][fragile]" value="1" @checked(! empty($ligne['fragile']))>
            Fragile
        </label>
        <p class="prix-ligne text-sm font-medium text-blue-600"></p>
        <button type="button" class="retirer-colis text-sm text-red-600 hover:underline">Retirer</button>
    </div>
</div>