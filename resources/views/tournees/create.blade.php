@extends('layouts.app')

@section('titre', 'Nouvelle tournée')

@section('contenu')
<div class="max-w-3xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <form action="{{ route('tournees.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label for="livreur_id" class="mb-1 block text-sm font-medium text-slate-700">Livreur (actifs uniquement)</label>
            <select id="livreur_id" name="livreur_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">-- Choisir --</option>
                @foreach ($livreurs as $livreur)
                    <option value="{{ $livreur->id }}" @selected(old('livreur_id') == $livreur->id)>
                        {{ $livreur->nom }} {{ $livreur->prenom }} · {{ $livreur->type_vehicule }}
                    </option>
                @endforeach
            </select>
            @error('livreur_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-3">
            <div>
                <label for="date" class="mb-1 block text-sm font-medium text-slate-700">Date</label>
                <input type="date" id="date" name="date" value="{{ old('date', now()->toDateString()) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="heure_depart" class="mb-1 block text-sm font-medium text-slate-700">Heure de départ</label>
                <input type="time" id="heure_depart" name="heure_depart" value="{{ old('heure_depart', '08:00') }}"
                       class="w-full rounded-lg border px-3 py-2 text-sm {{ $errors->has('heure_depart') ? 'border-red-400' : 'border-slate-300' }}">
                @error('heure_depart') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="heure_fin" class="mb-1 block text-sm font-medium text-slate-700">Heure de fin</label>
                <input type="time" id="heure_fin" name="heure_fin" value="{{ old('heure_fin', '17:00') }}"
                       class="w-full rounded-lg border px-3 py-2 text-sm {{ $errors->has('heure_fin') ? 'border-red-400' : 'border-slate-300' }}">
                @error('heure_fin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <p class="text-sm text-slate-500">La référence est générée automatiquement. Les colis s'ajoutent après la création.</p>

        <div class="flex gap-3">
            <button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Créer la tournée</button>
            <a href="{{ route('tournees.index') }}" class="rounded-lg px-5 py-2 text-sm text-slate-600 hover:bg-slate-100">Annuler</a>
        </div>
    </form>
</div>
@endsection