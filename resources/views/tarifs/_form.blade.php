@php $t = $tarif ?? null; @endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="zone_id" class="mb-1 block text-sm font-medium text-slate-700">Zone</label>
        <select id="zone_id" name="zone_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @foreach ($zones as $zone)
                <option value="{{ $zone->id }}" @selected(old('zone_id', $t?->zone_id ?? request('zone_id')) == $zone->id)>{{ $zone->nom }}</option>
            @endforeach
        </select>
        @error('zone_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    @foreach ([['poids_min', 'Poids minimum (kg)'], ['poids_max', 'Poids maximum (kg)']] as [$champ, $label])
        <div>
            <label for="{{ $champ }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
            <input type="number" step="0.01" min="0" id="{{ $champ }}" name="{{ $champ }}"
                   value="{{ old($champ, $t ? $t->$champ + 0 : '') }}"
                   class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none
                          {{ $errors->has($champ) ? 'border-red-400' : 'border-slate-300 focus:border-blue-500' }}">
            @error($champ) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    @endforeach

    <div>
        <label for="type_livraison" class="mb-1 block text-sm font-medium text-slate-700">Type de livraison</label>
        <select id="type_livraison" name="type_livraison" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @foreach (['STANDARD', 'EXPRESS'] as $v)
                <option value="{{ $v }}" @selected(old('type_livraison', $t?->type_livraison ?? 'STANDARD') === $v)>{{ $v }}</option>
            @endforeach
        </select>
        @error('type_livraison') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="priorite" class="mb-1 block text-sm font-medium text-slate-700">Priorité</label>
        <select id="priorite" name="priorite" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @foreach (['NORMALE', 'URGENTE'] as $v)
                <option value="{{ $v }}" @selected(old('priorite', $t?->priorite ?? 'NORMALE') === $v)>{{ $v }}</option>
            @endforeach
        </select>
        @error('priorite') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="prix" class="mb-1 block text-sm font-medium text-slate-700">Prix (Ar)</label>
        <input type="number" min="0" id="prix" name="prix" value="{{ old('prix', $t?->prix) }}"
               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none
                      {{ $errors->has('prix') ? 'border-red-400' : 'border-slate-300 focus:border-blue-500' }}">
        @error('prix') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Enregistrer</button>
    <a href="{{ route('tarifs.index') }}" class="rounded-lg px-5 py-2 text-sm text-slate-600 hover:bg-slate-100">Annuler</a>
</div>