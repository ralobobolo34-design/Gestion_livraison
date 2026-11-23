@php $z = $zone ?? null; @endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label for="nom" class="mb-1 block text-sm font-medium text-slate-700">Nom</label>
        <input type="text" id="nom" name="nom" value="{{ old('nom', $z?->nom) }}"
               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none
                      {{ $errors->has('nom') ? 'border-red-400' : 'border-slate-300 focus:border-blue-500' }}">
        @error('nom') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="tarif_base" class="mb-1 block text-sm font-medium text-slate-700">Tarif de base (Ar)</label>
        <input type="number" min="0" id="tarif_base" name="tarif_base" value="{{ old('tarif_base', $z?->tarif_base ?? 0) }}"
               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none
                      {{ $errors->has('tarif_base') ? 'border-red-400' : 'border-slate-300 focus:border-blue-500' }}">
        @error('tarif_base') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Description</label>
        <textarea id="description" name="description" rows="3"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">{{ old('description', $z?->description) }}</textarea>
        @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
    <div class="sm:col-span-2">
    <label for="quartiers" class="mb-1 block text-sm font-medium text-slate-700">Quartiers de la zone</label>
    <textarea id="quartiers" name="quartiers" rows="4" placeholder="Analakely, Ambohijatovo, Behoririka..."
              class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none
                     {{ $errors->has('quartiers') ? 'border-red-400' : 'border-slate-300 focus:border-blue-500' }}">{{ old('quartiers', $z?->quartiers->pluck('nom')->join(', ')) }}</textarea>
    <p class="mt-1 text-xs text-slate-400">Séparez les quartiers par une virgule ou un retour à la ligne. Un quartier n'appartient qu'à une seule zone.</p>
    @error('quartiers') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
</div>

    <div>
        <label for="statut" class="mb-1 block text-sm font-medium text-slate-700">Statut</label>
        <select id="statut" name="statut" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @foreach (['ACTIF', 'INACTIF'] as $s)
                <option value="{{ $s }}" @selected(old('statut', $z?->statut ?? 'ACTIF') === $s)>{{ $s }}</option>
            @endforeach
        </select>
        @error('statut') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Enregistrer</button>
    <a href="{{ route('zones.index') }}" class="rounded-lg px-5 py-2 text-sm text-slate-600 hover:bg-slate-100">Annuler</a>
</div>