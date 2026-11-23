@extends('layouts.app')

@section('titre', $colis->reference)

@section('contenu')
@php $suivants = \App\Services\ColisStatutService::suivants($colis->statut); @endphp

<div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
    <div class="mb-4 flex items-center gap-3">
        <h2 class="text-lg font-semibold text-slate-800">{{ $colis->reference }}</h2>
        <x-badge :statut="$colis->statut" />
    </div>

    <dl class="grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
        <div><dt class="text-slate-500">Commande</dt>
            <dd class="font-medium"><a href="{{ route('commandes.show', $colis->commande) }}" class="hover:underline">{{ $colis->commande->reference }}</a></dd></div>
        <div><dt class="text-slate-500">Client</dt><dd class="font-medium">{{ $colis->commande->client->nom }} {{ $colis->commande->client->prenom }}</dd></div>
        <div><dt class="text-slate-500">Téléphone</dt><dd class="font-medium">{{ $colis->commande->client->telephone ?? '-' }}</dd></div>
        <div><dt class="text-slate-500">Description</dt><dd class="font-medium">{{ $colis->description ?? '-' }}</dd></div>
        <div><dt class="text-slate-500">Poids</dt><dd class="font-medium">{{ $colis->poids + 0 }} kg</dd></div>
        <div><dt class="text-slate-500">Dimensions</dt><dd class="font-medium">{{ $colis->dimensions ?? '-' }}</dd></div>
        <div><dt class="text-slate-500">Valeur déclarée</dt><dd class="font-medium">{{ $colis->valeur_declaree ? number_format($colis->valeur_declaree, 0, ',', ' ') . ' Ar' : '-' }}</dd></div>
        <div><dt class="text-slate-500">Fragile</dt><dd class="font-medium">{{ $colis->fragile ? 'Oui' : 'Non' }}</dd></div>
        <div><dt class="text-slate-500">Prix</dt><dd class="font-medium">{{ number_format($colis->prix, 0, ',', ' ') }} Ar</dd></div>
        <div><dt class="text-slate-500">Tournée(s)</dt>
            <dd class="font-medium">
                {{ $colis->tournees->map(fn ($t) => $t->reference . ' (' . $t->livreur->nom . ')')->join(', ') ?: '-' }}
            </dd></div>
    </dl>

    @error('statut')
        <p class="mt-4 rounded-lg bg-red-100 px-4 py-2 text-sm text-red-800">{{ $message }}</p>
    @enderror

    <div class="mt-5 border-t border-slate-200 pt-4">
        @if ($suivants)
            <p class="mb-2 text-sm text-slate-500">Changer le statut :</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($suivants as $suivant)
                    <form action="{{ route('colis.statut', $colis) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="statut" value="{{ $suivant }}">
                        <button class="rounded-lg px-4 py-2 text-sm font-medium
                            {{ in_array($suivant, ['ANNULE', 'ECHEC_LIVRAISON']) ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-blue-600 text-white hover:bg-blue-700' }}">
                            {{ str_replace('_', ' ', $suivant) }}
                        </button>
                    </form>
                @endforeach
            </div>
        @else
            <p class="text-sm text-slate-500">Ce colis est au statut final : aucun changement n'est possible.</p>
        @endif
    </div>
</div>

<div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
    <h2 class="border-b border-slate-200 p-5 text-base font-semibold text-slate-800">Historique des statuts</h2>
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
            <tr>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Ancien statut</th>
                <th class="px-5 py-3">Nouveau statut</th>
                <th class="px-5 py-3">Utilisateur</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            @foreach ($colis->historiques->sortByDesc('id') as $h)
                <tr>
                    <td class="px-5 py-3">{{ $h->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-5 py-3">{{ $h->ancien_statut ? str_replace('_', ' ', $h->ancien_statut) : '-' }}</td>
                    <td class="px-5 py-3"><x-badge :statut="$h->nouveau_statut" /></td>
                    <td class="px-5 py-3">{{ $h->user?->name ?? 'Système' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection