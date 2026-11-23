@extends('layouts.app')

@section('titre', 'Modifier ' . $commande->reference)

@section('contenu')
<div class="max-w-3xl rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <form action="{{ route('commandes.update', $commande) }}" method="POST" class="space-y-5">
        @csrf
        @method('PUT')

        @foreach ([['adresse_depart', 'Adresse de départ'], ['adresse_destination', 'Adresse de destination']] as [$champ, $label])
            <div>
                <label for="{{ $champ }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $label }}</label>
                <input type="text" id="{{ $champ }}" name="{{ $champ }}" value="{{ old($champ, $commande->$champ) }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error($champ) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        @endforeach

        <div>
            <label for="date_livraison_prevue" class="mb-1 block text-sm font-medium text-slate-700">Date de livraison prévue</label>
            <input type="date" id="date_livraison_prevue" name="date_livraison_prevue"
                   value="{{ old('date_livraison_prevue', $commande->date_livraison_prevue?->format('Y-m-d')) }}"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @error('date_livraison_prevue') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <p class="text-sm text-slate-500">Le client, la zone, le type et les colis ne sont pas modifiables, car ils déterminent le prix.</p>

        <div class="flex gap-3">
            <button class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Enregistrer</button>
            <a href="{{ route('commandes.show', $commande) }}" class="rounded-lg px-5 py-2 text-sm text-slate-600 hover:bg-slate-100">Annuler</a>
        </div>
    </form>
</div>
@endsection