@php $l = $livreur ?? null; @endphp

<div class="grid gap-5 sm:grid-cols-2">
    @foreach ([['nom', 'Nom'], ['prenom', 'Prénom'], ['telephone', 'Téléphone'], ['email', 'Email'], ['numero_vehicule', 'Numéro du véhicule']] as [$champ, $label])
        <div>
            <label for="{{ $champ }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
            <input type="{{ $champ === 'email' ? 'email' : 'text' }}" id="{{ $champ }}" name="{{ $champ }}"
                   value="{{ old($champ, $l?->$champ) }}"
                   class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none
                          {{ $errors->has($champ) ? 'border-red-400' : 'border-slate-300 focus:border-blue-500' }}">
            @error($champ)
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endforeach

    <div>
        <label for="type_vehicule" class="mb-1 block text-sm font-medium text-slate-700">Type de véhicule</label>
        <select id="type_vehicule" name="type_vehicule" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @foreach (['MOTO', 'VOITURE', 'CAMION', 'AUTRE'] as $v)
                <option value="{{ $v }}" @selected(old('type_vehicule', $l?->type_vehicule) === $v)>{{ $v }}</option>
            @endforeach
        </select>
        @error('type_vehicule')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="statut" class="mb-1 block text-sm font-medium text-slate-700">Statut</label>
        <select id="statut" name="statut" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @foreach (['ACTIF', 'INACTIF', 'INDISPONIBLE'] as $s)
                <option value="{{ $s }}" @selected(old('statut', $l?->statut ?? 'ACTIF') === $s)>{{ $s }}</option>
            @endforeach
        </select>
        @error('statut')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Enregistrer</button>
    <a href="{{ route('livreurs.index') }}" class="rounded-lg px-5 py-2 text-sm text-slate-600 hover:bg-slate-100">Annuler</a>
</div>