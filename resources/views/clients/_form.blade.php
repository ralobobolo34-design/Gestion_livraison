@php $c = $client ?? null; @endphp

<div class="grid gap-5 sm:grid-cols-2">
    @foreach ([['nom', 'Nom'], ['prenom', 'Prénom'], ['telephone', 'Téléphone'], ['email', 'Email']] as [$champ, $label])
        <div>
            <label for="{{ $champ }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
            <input type="{{ $champ === 'email' ? 'email' : 'text' }}" id="{{ $champ }}" name="{{ $champ }}"
                   value="{{ old($champ, $c?->$champ) }}"
                   class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none
                          {{ $errors->has($champ) ? 'border-red-400' : 'border-slate-300 focus:border-blue-500' }}">
            @error($champ)
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endforeach

    <div class="sm:col-span-2">
        <label for="adresse" class="mb-1 block text-sm font-medium text-slate-700">Adresse</label>
        <input type="text" id="adresse" name="adresse" value="{{ old('adresse', $c?->adresse) }}"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        @error('adresse')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($c)
        <div>
            <label for="statut" class="mb-1 block text-sm font-medium text-slate-700">Statut</label>
            <select id="statut" name="statut" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="ACTIF" @selected(old('statut', $c->statut) === 'ACTIF')>Actif</option>
                <option value="INACTIF" @selected(old('statut', $c->statut) === 'INACTIF')>Inactif</option>
            </select>
        </div>
    @endif
</div>

<div class="mt-6 flex gap-3">
    <button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Enregistrer</button>
    <a href="{{ route('clients.index') }}" class="rounded-lg px-5 py-2 text-sm text-slate-600 hover:bg-slate-100">Annuler</a>
</div>